<?php

namespace App\Http\Controllers;

use App\Models\AssetBorrowing;
use App\Models\AssetBorrowingItem;
use App\Models\Item;
use App\Models\Lab;
use App\Models\AssetUnit;
use App\Models\InventoryBalance;
use App\Services\BorrowingDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AssetBorrowingController extends Controller
{
    /**
     * Show the form for creating a new asset borrowing request
     */
    public function create()
    {
        $labs = Lab::orderBy('name')->get();
        
        // Get borrowable items (items that can be borrowed)
        // Group by CATEGORY to simplify selection
        $items = Item::with(['assetTypeCode', 'batches', 'batches.inventoryBalances', 'batches.assetUnits'])
            ->where(function($query) {
                $query->whereHas('assetTypeCode', function($q) {
                    $q->where('is_borrowable', true);
                })
                ->orWhereDoesntHave('assetTypeCode');
            })
            ->get();

        // Group items by Category (or Name if category is null)
        $borrowableItems = $items->groupBy(function($item) {
            return $item->category ?? $item->name;
        })->map(function($group) {
            $first = $group->first();
            
            // Calculate total available stock for this entire CATEGORY
            $totalQuantity = $group->sum(function($item) {
                return $item->total_units; // Uses the accessor in Item model
            });

            // Use category name as the display name if available, otherwise item name
            $displayName = $first->category ?? $first->name;

            // Return plain array to ensure JSON serialization works perfectly
            return [
                'id' => $first->id,
                'name' => $first->name,
                'display_name' => $displayName,
                'category' => $first->category,
                'tracking_mode' => $first->tracking_mode,
                'total_available_quantity' => $totalQuantity,
                'is_grouped' => $group->count() > 1
            ];
        })->values()->sortBy('display_name')->values();

        return view('asset-borrowing.create', compact('labs', 'borrowableItems'));
    }

    /**
     * Store a newly created asset borrowing request
     */
    /**
     * Store a newly created asset borrowing request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'borrower_name' => 'required|string|max:255',
            'borrower_type' => 'required|in:Mahasiswa,Dosen,Tendik,Lainnya',
            'borrower_id_number' => 'nullable|string|max:50',
            'study_program' => 'nullable|string|max:255',
            'class_year' => 'nullable|digits:4',
            'position' => 'nullable|string|max:255',
            'phone_number' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'borrower_address' => 'required|string|max:500', // Made required per user request
            'lab_id' => 'nullable|exists:labs,id', // Made nullable
            'purpose' => 'required|string',
            'borrow_date' => 'required|date|after_or_equal:today',
            'return_date' => 'required|date|after_or_equal:borrow_date',
            'borrow_time' => 'nullable|date_format:H:i',
            'return_time' => 'nullable|date_format:H:i',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.lab_id' => 'nullable|exists:labs,id', // Add lab_id for aggregate items
            'items.*.asset_unit_id' => 'nullable|exists:asset_units,id',
            'items.*.quantity' => 'nullable|integer|min:1',
            'items.*.condition_good' => 'nullable|boolean',
            'items.*.condition_adequate' => 'nullable|boolean',
            'items.*.condition_complete' => 'nullable|boolean',
            'items.*.remarks' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Handle document upload
            $documentPath = null;
            if ($request->hasFile('document')) {
                $documentPath = $request->file('document')->store('asset-borrowing-documents', 'public');
            }

            // Determine primary lab_id from first item if not set (best effort)
            $primaryLabId = null;
            if (!empty($validated['items'])) {
                $firstItem = $validated['items'][0];
                if (isset($firstItem['lab_id'])) {
                    $primaryLabId = $firstItem['lab_id'];
                } elseif (isset($firstItem['asset_unit_id'])) {
                    $unit = AssetUnit::find($firstItem['asset_unit_id']);
                    $primaryLabId = $unit ? $unit->lab_id : null;
                }
            }

            // Create borrowing record
            $borrowing = AssetBorrowing::create([
                'borrower_name' => $validated['borrower_name'],
                'borrower_type' => $validated['borrower_type'],
                'borrower_id_number' => $validated['borrower_id_number'] ?? null,
                'study_program' => $validated['study_program'] ?? null,
                'class_year' => $validated['class_year'] ?? null,
                'position' => $validated['position'] ?? null,
                'phone_number' => $validated['phone_number'],
                'email' => $validated['email'] ?? null,
                'borrower_address' => $validated['borrower_address'] ?? null,
                'lab_id' => $primaryLabId,
                'purpose' => $validated['purpose'],
                'borrow_date' => $validated['borrow_date'],
                'return_date' => $validated['return_date'],
                'borrow_time' => $validated['borrow_time'] ?? null,
                'return_time' => $validated['return_time'] ?? null,
                'document_path' => $documentPath,
                'status' => 'pending',
                'tracking_token' => \Illuminate\Support\Str::random(10), // Ensure token is generated
            ]);

            // Create borrowing items
            foreach ($validated['items'] as $itemData) {
                // Determine target pool of items
                $primaryItem = Item::find($itemData['item_id']);
                
                $targetItemIds = collect([$primaryItem->id]);
                if ($primaryItem->category) {
                    $targetItemIds = Item::where('category', $primaryItem->category)
                        ->where('tracking_mode', $primaryItem->tracking_mode)
                        ->pluck('id');
                }
                
                // Common borrowing data template
                $baseBorrowingItemData = [
                    'asset_borrowing_id' => $borrowing->id,
                    // item_id will be set dynamically based on allocation
                    'brand_type' => null, 
                    'condition_good' => $itemData['condition_good'] ?? true,
                    'condition_adequate' => $itemData['condition_adequate'] ?? false,
                    'condition_complete' => $itemData['condition_complete'] ?? true,
                    'remarks' => $itemData['remarks'] ?? null,
                ];

                // Handle based on tracking mode
                if ($primaryItem->tracking_mode->value === 'AGGREGATE') {
                    $requestedQty = $itemData['quantity'] ?? 1;
                    
                    // Logic to find source lab/balance for aggregate items
                    // Priority:
                    // 1. If lab_id is specified (legacy/specific), use that.
                    // 2. Fallback to any brand/model in category with enough quantity.
                    
                    $remainingQty = $requestedQty;
                    
                    if (isset($itemData['lab_id'])) {
                        // Specific lab requested (rare case in new flow, but handled)
                        // Should we restrict to primary item or allow category? 
                        // Let's allow category in that lab.
                         $balance = InventoryBalance::where('lab_id', $itemData['lab_id'])
                            ->whereHas('batch', function($q) use ($targetItemIds) {
                                $q->whereIn('item_id', $targetItemIds);
                            })
                            ->where('condition', 'BAIK')
                            ->first();

                        if ($balance) {
                             $newItemData = $baseBorrowingItemData;
                             $newItemData['item_id'] = $balance->batch->item_id; // Use actual item ID
                             $newItemData['inventory_balance_id'] = $balance->id;
                             $newItemData['quantity'] = $remainingQty;
                             AssetBorrowingItem::create($newItemData);
                        }
                    } else {
                        // Automatic assignment
                        // Fetch ALL candidates in category, Sort by quantity desc
                        
                        $candidates = InventoryBalance::with('batch')
                           ->whereHas('batch', function($q) use ($targetItemIds) {
                               $q->whereIn('item_id', $targetItemIds);
                           })
                           ->where('condition', 'BAIK')
                           ->where('quantity', '>', 0)
                           ->orderBy('quantity', 'desc')
                           ->get();
                           
                        $toAllocate = $itemData['quantity'] ?? 1;
                        
                        foreach ($candidates as $balance) {
                            if ($toAllocate <= 0) break;
                            
                            $take = min($toAllocate, $balance->quantity);
                            
                            $newItemData = $baseBorrowingItemData;
                            $newItemData['item_id'] = $balance->batch->item_id; // Use actual item ID
                            $newItemData['inventory_balance_id'] = $balance->id;
                            $newItemData['quantity'] = $take;
                            
                            AssetBorrowingItem::create($newItemData);
                            
                            $toAllocate -= $take;
                            
                            if (!$primaryLabId) $primaryLabId = $balance->lab_id;
                        }
                        
                        if ($toAllocate > 0) {
                            // Could not fulfill full quantity. 
                        }
                    }
                } else {
                    // STRUCTURED or SEAT - allocate units based on quantity
                    $quantity = $itemData['quantity'] ?? 1;
                    
                    // Find available units automatically in category
                    $availableUnits = AssetUnit::whereHas('batch', function($q) use ($targetItemIds) {
                            $q->whereIn('item_id', $targetItemIds);
                        })
                        ->with('batch')
                        ->where('condition', 'BAIK')
                        ->where('is_available', true)
                        ->limit($quantity)
                        ->get();
                    
                    if ($availableUnits->count() < $quantity) {
                        throw new \Exception("Tidak cukup unit tersedia untuk Kategori {$primaryItem->category} ({$primaryItem->name}). Diminta: {$quantity}, Tersedia: {$availableUnits->count()}");
                    }
                    
                    $allocatedUnits = $availableUnits;
                    
                    // Create borrowing item for each unit
                    foreach ($allocatedUnits as $unit) {
                        $newItemData = $baseBorrowingItemData;
                        $newItemData['item_id'] = $unit->batch->item_id; // Use actual item ID
                        $newItemData['asset_unit_id'] = $unit->id;
                        $newItemData['quantity'] = 1;
                        
                        AssetBorrowingItem::create($newItemData);
                        
                        if (!$primaryLabId) {
                            $primaryLabId = $unit->lab_id;
                        }
                    }
                    
                    // Skip the create below since we already created items in the loop
                    continue;
                }
            }
            
            // Update the main borrowing record with the primary lab ID if it was found later
            if ($primaryLabId && !$borrowing->lab_id) {
                $borrowing->update(['lab_id' => $primaryLabId]);
            }

            DB::commit();

            return redirect()->route('asset-borrowing.success', $borrowing->id)
                ->with('success', 'Permohonan peminjaman aset berhasil diajukan!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if (isset($documentPath)) {
                Storage::disk('public')->delete($documentPath);
            }

            return back()->withInput()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Show success page
     */
    public function success($id)
    {
        $borrowing = AssetBorrowing::with(['lab', 'borrowedItems.item', 'borrowedItems.assetUnit'])
            ->findOrFail($id);

        return view('asset-borrowing.success', compact('borrowing'));
    }

    /**
     * Get available assets for a specific item across all labs
     */
    public function getAvailableAssets(Request $request)
    {
        $itemId = $request->item_id;

        if (!$itemId) {
            return response()->json(['error' => 'Item ID is required'], 400);
        }

        $item = Item::with('assetTypeCode')->find($itemId);
        
        if (!$item) {
            return response()->json(['error' => 'Item not found'], 404);
        }

        // Determine items to query: either just this one, or all in category
        // We ensure consistent tracking mode to avoid mixing data structures
        if ($item->category) {
             $itemIds = Item::where('category', $item->category)
                ->where('tracking_mode', $item->tracking_mode)
                ->pluck('id');
        } else {
             $itemIds = collect([$itemId]);
        }

        $data = [];

        if ($item->tracking_mode->value === 'AGGREGATE') {
            // For aggregate items, return available quantity per lab, summed across category
            $balances = InventoryBalance::with('lab')
                ->whereHas('batch', function($q) use ($itemIds) {
                    $q->whereIn('item_id', $itemIds);
                })
                ->where('condition', 'BAIK')
                ->where('quantity', '>', 0)
                ->get();
            
            // Group by Lab and sum quantity
            $labs = $balances->groupBy('lab_id')->map(function($group) {
                return [
                    'lab_id' => $group->first()->lab_id,
                    'lab_name' => $group->first()->lab->name,
                    'available_quantity' => $group->sum('quantity')
                ];
            })->values();

            $data = [
                'type' => 'aggregate',
                'labs' => $labs,
            ];
        } else {
            // For structured tag and seat number, return available units with lab info
            $units = AssetUnit::with(['batch', 'lab'])
                ->where('condition', 'BAIK')
                ->where('is_available', true)
                ->whereHas('batch', function($q) use ($itemIds) {
                    $q->whereIn('item_id', $itemIds);
                })
                ->get()
                ->map(function($unit) {
                    return [
                        'id' => $unit->id,
                        'asset_tag' => $unit->asset_tag,
                        'subtype' => $unit->subtype,
                        'lab_id' => $unit->lab_id,
                        'lab_name' => $unit->lab->name,
                    ];
                });

            $data = [
                'type' => $item->tracking_mode->value === 'STRUCTURED_TAG' ? 'structured' : 'seat',
                'units' => $units,
            ];
        }

        return response()->json($data);
    }

    /**
     * Admin: Show all borrowing requests
     */
    public function index()
    {
        $borrowings = AssetBorrowing::with(['lab', 'borrowedItems'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.asset-borrowings.index', compact('borrowings'));
    }

    /**
     * Admin: Show borrowing detail and form to complete document
     */
    public function show($id)
    {
        $borrowing = AssetBorrowing::with([
            'lab', 
            'borrowedItems.item', 
            'borrowedItems.assetUnit',
            'approvedBy',
            'handedOutBy',
            'receivedBackBy'
        ])->findOrFail($id);

        return view('admin.asset-borrowings.show', compact('borrowing'));
    }

    /**
     * Admin: Update first party data and generate document
     */
    public function updateFirstParty(Request $request, $id, BorrowingDocumentService $documentService)
    {
        $borrowing = AssetBorrowing::findOrFail($id);

        $validated = $request->validate([
            'first_party_name' => 'required|string|max:255',
            'first_party_position' => 'required|string|max:255',
            'first_party_address' => 'required|string|max:500',
            'first_party_phone' => 'required|string|max:20',
            'document_date' => 'nullable|date',
        ]);

        try {
            // Update first party data
            $documentService->updateFirstPartyData($borrowing, $validated);

            // Generate PDF
            $pdfPath = $documentService->generatePDF($borrowing);

            return redirect()->route('admin.asset-borrowings.show', $id)
                ->with('success', 'Data PIHAK PERTAMA berhasil disimpan dan surat peminjaman telah dibuat!');

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Admin: Download generated document
     */
    public function downloadDocument($id)
    {
        $borrowing = AssetBorrowing::findOrFail($id);

        if (!$borrowing->generated_document_path) {
            return back()->withErrors(['error' => 'Surat peminjaman belum dibuat.']);
        }

        $filePath = storage_path('app/public/' . $borrowing->generated_document_path);

        if (!file_exists($filePath)) {
            return back()->withErrors(['error' => 'File tidak ditemukan.']);
        }

        return response()->download($filePath, 'Surat-Peminjaman-' . $borrowing->document_number . '.pdf');
    }

    /**
     * Admin: Preview generated document
     */
    public function previewDocument($id, BorrowingDocumentService $documentService)
    {
        $borrowing = AssetBorrowing::with(['borrowedItems.item', 'borrowedItems.assetUnit', 'lab'])
            ->findOrFail($id);

        // Generate PDF preview
        $borrowing->load(['borrowedItems.item', 'borrowedItems.assetUnit', 'lab']);
        
        $documentDate = $borrowing->document_date ?? now();
        
        $data = [
            'borrowing' => $borrowing,
            'items' => $borrowing->borrowedItems,
            'documentDate' => $documentService->formatIndonesianDate($documentDate),
            'documentFullDate' => $documentService->formatFullIndonesianDate($documentDate),
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.borrowing-document', $data);
        
        return $pdf->stream('preview-surat-peminjaman.pdf');
    }

    /**
     * Admin: Approve borrowing request
     */
    public function approve($id)
    {
        $borrowing = AssetBorrowing::findOrFail($id);

        $borrowing->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Permohonan peminjaman telah disetujui!');
    }

    /**
     * Admin: Reject borrowing request
     */
    public function reject(Request $request, $id)
    {
        $borrowing = AssetBorrowing::findOrFail($id);

        $validated = $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        $borrowing->update([
            'status' => 'rejected',
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return back()->with('success', 'Permohonan peminjaman telah ditolak.');
    }

    /**
     * Admin: Hand out assets to borrower
     */
    public function handout(Request $request, $id)
    {
        $borrowing = AssetBorrowing::findOrFail($id);

        if ($borrowing->status !== 'approved') {
            return back()->with('error', 'Hanya peminjaman yang sudah disetujui yang dapat diserahkan.');
        }

        $validated = $request->validate([
            'borrow_condition_notes' => 'nullable|string',
        ]);

        $borrowing->update([
            'status' => 'borrowed',
            'handed_out_by' => auth()->id(),
            'handed_out_at' => now(),
            'borrow_condition_notes' => $validated['borrow_condition_notes'] ?? null,
        ]);

        // Mark asset units as unavailable
        foreach ($borrowing->borrowedItems as $item) {
            if ($item->asset_unit_id) {
                AssetUnit::where('id', $item->asset_unit_id)->update(['is_available' => false]);
            }
        }

        return back()->with('success', 'Barang berhasil diserahkan kepada peminjam!');
    }

    /**
     * Admin: Receive returned assets from borrower
     */
    public function receive(Request $request, $id)
    {
        $borrowing = AssetBorrowing::findOrFail($id);

        if ($borrowing->status !== 'borrowed') {
            return back()->with('error', 'Hanya peminjaman yang sudah diserahkan yang dapat dikembalikan.');
        }

        $validated = $request->validate([
            'return_condition_notes' => 'nullable|string',
        ]);

        $borrowing->update([
            'status' => 'returned',
            'received_back_by' => auth()->id(),
            'received_back_at' => now(),
            'return_condition_notes' => $validated['return_condition_notes'] ?? null,
        ]);

        // Mark asset units as available again
        foreach ($borrowing->borrowedItems as $item) {
            if ($item->asset_unit_id) {
                AssetUnit::where('id', $item->asset_unit_id)->update(['is_available' => true]);
            }
        }

        return back()->with('success', 'Barang berhasil diterima kembali!');
    }
}
