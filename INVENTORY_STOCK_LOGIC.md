# Logic Stok Inventaris - Sistem Peminjaman Barang

## Alur Peminjaman dan Dampak ke Stok

### 1. User Submit Peminjaman
- **Status**: `pending`
- **Dampak Stok**: ❌ Tidak ada pengurangan stok
- **Alasan**: Peminjaman belum disetujui, bisa saja ditolak

### 2. Admin Approve Peminjaman
- **Status**: `approved`
- **Dampak Stok**: ❌ Tidak ada pengurangan stok
- **Alasan**: Barang belum diserahkan, masih di gudang

### 3. Admin Handout (Serahkan Barang)
- **Status**: `borrowed`
- **Dampak Stok**: ✅ **STOK BERKURANG**

#### Untuk Aggregate Items:
```php
// Kurangi quantity di inventory_balances
$balance->decrement('quantity', $item->quantity);
```
- Quantity di `inventory_balances` berkurang sesuai jumlah yang dipinjam
- Contoh: Speaker 10 unit → dipinjam 2 → sisa 8 unit

#### Untuk Structured/Seat Items:
```php
// Set is_available = false
AssetUnit::where('id', $unitId)->update(['is_available' => false]);
```
- Unit yang dipinjam di-mark sebagai tidak tersedia
- Contoh: HP Pavilion #001 dipinjam → is_available = false

### 4. Admin Receive (Terima Kembali)
- **Status**: `returned`
- **Dampak Stok**: ✅ **STOK KEMBALI**

#### Untuk Aggregate Items:
**Kondisi BAIK**:
```php
// Kembalikan ke balance yang sama
$balance->increment('quantity', $item->quantity);
```

**Kondisi RUSAK_RINGAN/RUSAK_BERAT**:
```php
// Pindahkan ke balance dengan condition MAINTENANCE/RUSAK
$damagedBalance->increment('quantity', $item->quantity);
```
- Quantity tetap ada tapi di kondisi berbeda
- Tidak muncul di list peminjaman karena hanya BAIK yang bisa dipinjam

**Kondisi HILANG**:
```php
// Tidak ada penambahan quantity - benar-benar hilang
```

#### Untuk Structured/Seat Items:
**Kondisi BAIK**:
```php
AssetUnit::update(['is_available' => true, 'condition' => 'BAIK']);
```

**Kondisi RUSAK**:
```php
AssetUnit::update(['is_available' => true, 'condition' => 'RUSAK']);
```
- Masih available tapi condition berubah
- Tidak muncul di list peminjaman karena hanya BAIK yang bisa dipinjam

**Kondisi HILANG**:
```php
AssetUnit::update(['is_available' => false, 'condition' => 'RUSAK']);
```

### 5. Admin Reject Peminjaman
- **Status**: `rejected`
- **Dampak Stok**: ❌ Tidak ada perubahan stok
- **Alasan**: Peminjaman tidak pernah terjadi

## Accessor available_units di Model Item

Accessor ini menghitung jumlah unit yang **benar-benar tersedia** untuk dipinjam:

### Untuk Aggregate Items:
```php
InventoryBalance::whereHas('batch', function($query) {
        $query->where('item_id', $this->id);
    })
    ->where('condition', 'BAIK')  // ✅ Hanya kondisi BAIK
    ->where('quantity', '>', 0)    // ✅ Stok harus > 0
    ->sum('quantity');
```

### Untuk Structured/Seat Items:
```php
AssetUnit::whereHas('batch', function($query) {
        $query->where('item_id', $this->id);
    })
    ->where('is_available', true)  // ✅ Hanya yang available
    ->where('condition', 'BAIK')   // ✅ Hanya kondisi BAIK
    ->count();
```

## Test Cases

### Test Case 1: Peminjaman Normal
1. **Initial**: Speaker 10 unit tersedia
2. **User submit**: Speaker masih 10 unit ✅
3. **Admin approve**: Speaker masih 10 unit ✅
4. **Admin handout 2 unit**: Speaker jadi 8 unit ✅
5. **User lain buka form**: Hanya melihat 8 unit ✅
6. **Admin receive 2 unit (BAIK)**: Speaker kembali 10 unit ✅

### Test Case 2: Barang Rusak
1. **Initial**: Mouse 10 unit tersedia
2. **Admin handout 3 unit**: Mouse jadi 7 unit ✅
3. **Admin receive**:
   - 2 unit BAIK → balance BAIK +2 → Mouse BAIK jadi 9 unit ✅
   - 1 unit RUSAK_RINGAN → balance MAINTENANCE +1 → Mouse BAIK tetap 9 unit ✅
4. **User buka form**: Hanya melihat 9 unit (yang BAIK) ✅

### Test Case 3: Barang Hilang
1. **Initial**: Proyektor 5 unit tersedia
2. **Admin handout 1 unit**: Proyektor jadi 4 unit ✅
3. **Admin receive (HILANG)**: Proyektor tetap 4 unit ✅ (tidak kembali)

### Test Case 4: Race Condition Prevention
⚠️ **Note**: Untuk production, perlu tambahan locking mechanism untuk mencegah:
- 2 user meminjam unit yang sama secara bersamaan
- Overselling (meminjam melebihi stok tersedia)

Gunakan database transaction dan row-level locking:
```php
DB::transaction(function() {
    $balance = InventoryBalance::lockForUpdate()->find($id);
    // ... proses peminjaman
});
```

## Summary

✅ **Stok berkurang saat diserahkan (borrowed)**
✅ **Stok kembali saat dikembalikan (returned)**
✅ **Stok yang ditampilkan di form peminjaman sudah akurat**
✅ **Barang rusak/hilang tidak muncul di list tersedia**
✅ **Peminjaman yang pending/approved tidak mengurangi stok**

