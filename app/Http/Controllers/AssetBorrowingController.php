<?php

namespace App\Http\Controllers;

use App\Models\AssetBorrowing;
use App\Models\AssetBorrowingItem;
use App\Models\Item;
use App\Models\Lab;
use App\Models\AssetUnit;
use App\Models\InventoryBalance;
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'borrower_name' => 'required|string|max:255',
            'borrower_type' => 'required|in:Mahasiswa,Dosen,Staff,Lainnya',
            'borrower_id_number' => 'nullable|string|max:50',
            'study_program' => 'nullable|string|max:255',
            'class_year' => 'nullable|digits:4',
            'position' => 'nullable|string|max:255',
            'phone_number' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'lab_id' => 'required|exists:labs,id',
            'purpose' => 'required|string',
            'borrow_date' => 'required|date|after_or_equal:today',
            'return_date' => 'required|date|after_or_equal:borrow_date',
            'borrow_time' => 'nullable|date_format:H:i',
            'return_time' => 'nullable|date_format:H:i',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.asset_unit_id' => 'nullable|exists:asset_units,id',
            'items.*.quantity' => 'nullable|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            // Handle document upload
            $documentPath = null;
            if ($request->hasFile('document')) {
                $documentPath = $request->file('document')->store('asset-borrowing-documents', 'public');
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
                'lab_id' => $validated['lab_id'],
                'purpose' => $validated['purpose'],
                'borrow_date' => $validated['borrow_date'],
                'return_date' => $validated['return_date'],
                'borrow_time' => $validated['borrow_time'] ?? null,
                'return_time' => $validated['return_time'] ?? null,
                'document_path' => $documentPath,
                'status' => 'pending',
            ]);

            // Create borrowing items
            foreach ($validated['items'] as $itemData) {
                $item = Item::find($itemData['item_id']);
                
                $borrowingItemData = [
                    'asset_borrowing_id' => $borrowing->id,
                    'item_id' => $itemData['item_id'],
                ];

                // Handle based on tracking mode
                if ($item->tracking_mode->value === 'AGGREGATE') {
                    // For aggregate items
                    $borrowingItemData['quantity'] = $itemData['quantity'] ?? 1;
                } else {
                    // For structured tag and seat number
                    $borrowingItemData['asset_unit_id'] = $itemData['asset_unit_id'] ?? null;
                    $borrowingItemData['quantity'] = 1;
                }

                AssetBorrowingItem::create($borrowingItemData);
            }

            DB::commit();

            return redirect()->route('asset-borrowing.success', ['id' => $borrowing->id])
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
     * Get available assets for a specific lab and item
     */
    public function getAvailableAssets(Request $request)
    {
        $labId = $request->lab_id;
        $itemId = $request->item_id;

        if (!$labId || !$itemId) {
            return response()->json(['error' => 'Lab and Item are required'], 400);
        }

        $item = Item::with('assetTypeCode')->find($itemId);
        
        if (!$item) {
            return response()->json(['error' => 'Item not found'], 404);
        }

        $data = [];

        if ($item->tracking_mode->value === 'AGGREGATE') {
            // For aggregate items, return total available quantity
            $balance = InventoryBalance::where('lab_id', $labId)
                ->whereHas('batch', function($q) use ($itemId) {
                    $q->where('item_id', $itemId);
                })
                ->where('condition', 'BAIK')
                ->sum('quantity');

            $data = [
                'type' => 'aggregate',
                'available_quantity' => $balance,
            ];
        } else {
            // For structured tag and seat number, return available units
            $units = AssetUnit::with('batch')
                ->where('lab_id', $labId)
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
                    ];
                });

            $data = [
                'type' => $item->tracking_mode->value === 'STRUCTURED_TAG' ? 'structured' : 'seat',
                'units' => $units,
            ];
        }

        return response()->json($data);
    }
}
