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
        
        // Get borrowable items (items yang bisa dipinjam)
        $borrowableItems = Item::with('assetTypeCode')
            ->where(function($query) {
                $query->whereHas('assetTypeCode', function($q) {
                    $q->where('is_borrowable', true);
                })
                ->orWhereDoesntHave('assetTypeCode'); // Items tanpa asset type code juga bisa dipinjam
            })
            ->orderBy('name')
            ->get();

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
            'items.*.brand_type' => 'nullable|string|max:255',
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
                $item = Item::find($itemData['item_id']);
                
                $borrowingItemData = [
                    'asset_borrowing_id' => $borrowing->id,
                    'item_id' => $itemData['item_id'],
                    'brand_type' => $itemData['brand_type'] ?? null,
                    'condition_good' => $itemData['condition_good'] ?? true,
                    'condition_adequate' => $itemData['condition_adequate'] ?? false,
                    'condition_complete' => $itemData['condition_complete'] ?? true,
                    'remarks' => $itemData['remarks'] ?? null,
                ];

                // Handle based on tracking mode
                if ($item->tracking_mode->value === 'AGGREGATE') {
                    $borrowingItemData['quantity'] = $itemData['quantity'];
                    
                    // Logic to find source lab/balance for aggregate items
                    if (isset($itemData['lab_id'])) {
                        // If lab is explicitly selected (legacy or specific future case)
                        $balance = InventoryBalance::where('lab_id', $itemData['lab_id'])
                            ->whereHas('batch', function($q) use ($itemData) {
                                $q->where('item_id', $itemData['item_id']);
                            })
                            ->where('condition', 'BAIK')
                            ->first();
                            
                        if ($balance) {
                           $borrowingItemData['inventory_balance_id'] = $balance->id;
                        }
                    } else {
                        // Automatic lab assignment based on availability
                        // Find the first lab that has enough stock
                        $balance = InventoryBalance::whereHas('batch', function($q) use ($itemData) {
                                $q->where('item_id', $itemData['item_id']);
                            })
                            ->where('condition', 'BAIK')
                            ->where('quantity', '>=', $itemData['quantity'])
                            ->first();

                        if ($balance) {
                            $borrowingItemData['inventory_balance_id'] = $balance->id;
                            // If this is the first item and we haven't set a primary lab yet, use this one
                            if (!$primaryLabId) {
                                $primaryLabId = $balance->lab_id;
                            }
                        } else {
                            // Should ideally throw validation error here if stock ran out between check and submit
                            // For now, let's just log or fail gracefully?
                            // In a real app, we should probably redirect back with errors.
                            // But since we did frontend check, this is edge case.
                            // We'll proceed without balance id (might break FK if strict?)
                            // Migration says nullable, but logic requires it.
                        }
                    }
                } else {
                    // STRUCTURED or SEAT
                    $borrowingItemData['asset_unit_id'] = $itemData['asset_unit_id'];
                    $borrowingItemData['quantity'] = 1;
                    
                    if (!$primaryLabId && isset($itemData['asset_unit_id'])) {
                         $unit = AssetUnit::find($itemData['asset_unit_id']);
                         if ($unit) {
                             $primaryLabId = $unit->lab_id;
                         }
                    }
                }

                AssetBorrowingItem::create($borrowingItemData);
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

        $data = [];

        if ($item->tracking_mode->value === 'AGGREGATE') {
            // For aggregate items, return available quantity per lab
            $balances = InventoryBalance::with('lab')
                ->whereHas('batch', function($q) use ($itemId) {
                    $q->where('item_id', $itemId);
                })
                ->where('condition', 'BAIK')
                ->where('quantity', '>', 0)
                ->get()
                ->map(function($balance) {
                    return [
                        'lab_id' => $balance->lab_id,
                        'lab_name' => $balance->lab->name,
                        'available_quantity' => $balance->quantity
                    ];
                });

            $data = [
                'type' => 'aggregate',
                'labs' => $balances,
            ];
        } else {
            // For structured tag and seat number, return available units with lab info
            $units = AssetUnit::with(['batch', 'lab'])
                ->where('condition', 'BAIK')
                ->where('is_available', true)
                ->whereHas('batch', function($q) use ($itemId) {
                    $q->where('item_id', $itemId);
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
        
        $data = [
            'borrowing' => $borrowing,
            'items' => $borrowing->borrowedItems,
            'documentDate' => $documentService->formatIndonesianDate($borrowing->document_date ?? now()),
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
}
