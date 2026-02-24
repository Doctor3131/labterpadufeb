<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\AssetBorrowingController;

// Public Routes
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Feedback Routes (Public)
Route::post('/feedback', [FeedbackController::class, 'store'])
    ->middleware('throttle:5,1') // Max 5 submissions per minute
    ->name('feedback.store');

// Booking Routes (Public - No Authentication Required)
// Rate limited to prevent spam submissions
Route::get('/booking', [BookingController::class, 'create'])->name('booking.create');
Route::post('/booking', [BookingController::class, 'store'])
    ->middleware('throttle:10,1') // Max 10 submissions per minute
    ->name('booking.store');
Route::get('/booking/success/{token}', [BookingController::class, 'success'])->name('booking.success');
Route::post('/booking/available-labs', [BookingController::class, 'getAvailableLabs'])
    ->middleware('throttle:60,1') // Max 60 requests per minute (for AJAX)
    ->name('booking.available-labs');

// Lab Availability API (rate limited)
Route::get('/api/labs/available', [App\Http\Controllers\LabController::class, 'checkAvailability'])
    ->middleware('throttle:60,1')
    ->name('api.labs.available');

// PDF Print (for re-download)
Route::get('/booking/print/{token}', [BookingController::class, 'print'])->name('booking.print');

// Asset Borrowing Routes (Public - No Authentication Required)
Route::get('/asset-borrowing', [AssetBorrowingController::class, 'create'])->name('asset-borrowing.create');
Route::post('/asset-borrowing', [AssetBorrowingController::class, 'store'])
    ->middleware('throttle:10,1') // Max 10 submissions per minute
    ->name('asset-borrowing.store');
Route::get('/asset-borrowing/success/{id}', [AssetBorrowingController::class, 'success'])->name('asset-borrowing.success');
Route::get('/asset-borrowing/available-assets', [AssetBorrowingController::class, 'getAvailableAssets'])
    ->middleware('throttle:60,1')
    ->name('asset-borrowing.available-assets');

// Schedule Routes (Public)
Route::get('/schedules', function () {
    return view('schedules.index');
})->name('schedules.index');
Route::get('/schedules/week', [App\Http\Controllers\ScheduleController::class, 'getWeekSchedules'])->name('schedules.week');

// TV Display Mode (Fullscreen for TV/Monitor)
Route::get('/display', [App\Http\Controllers\ScheduleController::class, 'display'])->name('schedules.display');

// Auth Routes (rate limited to prevent brute force)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:10,1'); // Max 10 login attempts per minute

// Protected Routes (Admin/Super Admin only)
// Uses 'admin' middleware to explicitly check role, not just authentication
Route::middleware(['auth', 'admin'])->group(function () {
    // Redirect /dashboard to admin dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Profile / Account Management
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');
    
    // Admin Booking Management
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/bookings/{id}', [AdminController::class, 'show'])->name('admin.booking.show');
    Route::post('/admin/bookings/{id}/approve', [AdminController::class, 'approve'])->name('admin.booking.approve');
    Route::post('/admin/bookings/{id}/reject', [AdminController::class, 'reject'])->name('admin.booking.reject');
    
    // Admin Schedule CRUD
    Route::post('/admin/schedules/available-labs', [App\Http\Controllers\Admin\ScheduleController::class, 'getAvailableLabs'])
        ->name('admin.schedules.available-labs');
    Route::resource('/admin/schedules', App\Http\Controllers\Admin\ScheduleController::class)
        ->names('admin.schedules')
        ->except(['show']);

    // Admin Inventory Management (Global overview)
    Route::get('/admin/inventory', [App\Http\Controllers\Admin\LabInventoryController::class, 'globalIndex'])
        ->name('admin.inventory.index');

    // Admin Inventory Management (Lab-based)
    Route::get('/admin/labs/{lab}/inventory', [App\Http\Controllers\Admin\LabInventoryController::class, 'index'])
        ->name('admin.labs.inventory');
    Route::get('/admin/labs/{lab}/inventory/ledger', [App\Http\Controllers\Admin\LabInventoryController::class, 'ledger'])
        ->name('admin.labs.inventory.ledger');
    Route::get('/admin/labs/{lab}/inventory/create', [App\Http\Controllers\Admin\LabInventoryController::class, 'create'])
        ->name('admin.labs.inventory.create');
    Route::post('/admin/labs/{lab}/inventory', [App\Http\Controllers\Admin\LabInventoryController::class, 'store'])
        ->name('admin.labs.inventory.store');
    Route::get('/admin/labs/{lab}/inventory/{item}/units', [App\Http\Controllers\Admin\LabInventoryController::class, 'showUnits'])
        ->name('admin.labs.inventory.units');
    Route::get('/admin/labs/{lab}/inventory/{item}/balances', [App\Http\Controllers\Admin\LabInventoryController::class, 'showBalances'])
        ->name('admin.labs.inventory.balances');
        
    // Lab Inventory Logs (Mutasi Barang - Global)
    Route::get('/admin/inventory-logs', [App\Http\Controllers\Admin\InventoryLogController::class, 'index'])
        ->name('admin.inventory.logs.index');
    Route::get('/admin/inventory-logs/create', [App\Http\Controllers\Admin\InventoryLogController::class, 'create'])
        ->name('admin.inventory.logs.create');
    Route::post('/admin/inventory-logs', [App\Http\Controllers\Admin\InventoryLogController::class, 'store'])
        ->name('admin.inventory.logs.store');
    Route::get('/admin/inventory-logs/{log}/edit', [App\Http\Controllers\Admin\InventoryLogController::class, 'edit'])
        ->name('admin.inventory.logs.edit');
    Route::put('/admin/inventory-logs/{log}', [App\Http\Controllers\Admin\InventoryLogController::class, 'update'])
        ->name('admin.inventory.logs.update');
    Route::patch('/admin/inventory-logs/{log}/toggle-flow', [App\Http\Controllers\Admin\InventoryLogController::class, 'toggleFlow'])
        ->name('admin.inventory.logs.toggle-flow');
    Route::delete('/admin/inventory-logs/{log}', [App\Http\Controllers\Admin\InventoryLogController::class, 'destroy'])
        ->name('admin.inventory.logs.destroy');

    Route::post('/admin/inventory/bulk-update-condition', [App\Http\Controllers\Admin\LabInventoryController::class, 'bulkUpdateCondition'])
        ->name('admin.inventory.bulk-condition');
    Route::post('/admin/labs/{lab}/inventory/transfer-balance', [App\Http\Controllers\Admin\LabInventoryController::class, 'transferBalance'])
        ->name('admin.labs.inventory.transfer');
    Route::get('/admin/items/{item}/batches', [App\Http\Controllers\Admin\LabInventoryController::class, 'getBatches'])
        ->name('admin.items.batches');
    
    // Delete inventory routes
    Route::delete('/admin/labs/{lab}/inventory/{item}', [App\Http\Controllers\Admin\LabInventoryController::class, 'destroyItem'])
        ->name('admin.labs.inventory.destroy');
    Route::delete('/admin/inventory/units/{unit}', [App\Http\Controllers\Admin\LabInventoryController::class, 'destroyUnit'])
        ->name('admin.inventory.units.destroy');
    Route::post('/admin/inventory/bulk-delete-units', [App\Http\Controllers\Admin\LabInventoryController::class, 'bulkDestroyUnits'])
        ->name('admin.inventory.bulk-delete');
    Route::patch('/admin/inventory/units/{unit}/university-code', [App\Http\Controllers\Admin\LabInventoryController::class, 'updateUniversityCode'])
        ->name('admin.inventory.units.update-university-code');
    Route::patch('/admin/inventory/units/{unit}/asset-tag', [App\Http\Controllers\Admin\LabInventoryController::class, 'updateAssetTag'])
        ->name('admin.inventory.units.update-asset-tag');
    Route::patch('/admin/inventory/balance/{balance}/university-code', [App\Http\Controllers\Admin\LabInventoryController::class, 'updateUniversityCodeBalance'])
        ->name('admin.inventory.balance.update-university-code');

    // Transfer inventory (e.g. to Gudang) routes
    Route::post('/admin/inventory/bulk-transfer-units', [App\Http\Controllers\Admin\LabInventoryController::class, 'bulkTransferUnits'])
        ->name('admin.inventory.bulk-transfer');
    Route::post('/admin/labs/{lab}/inventory/transfer-aggregate', [App\Http\Controllers\Admin\LabInventoryController::class, 'transferAggregate'])
        ->name('admin.labs.inventory.transfer-aggregate');
        
    // Unified Transfer Form
    Route::get('/admin/inventory/transfer', [App\Http\Controllers\Admin\InventoryTransferController::class, 'create'])
        ->name('admin.inventory.transfer.create');
    Route::post('/admin/inventory/transfer', [App\Http\Controllers\Admin\InventoryTransferController::class, 'store'])
        ->name('admin.inventory.transfer.store');
        
    // API for Unified Transfer Form
    Route::get('/admin/api/inventory/{lab}/items', [App\Http\Controllers\Admin\InventoryTransferController::class, 'getItems']);
    Route::get('/admin/api/inventory/{lab}/items/{item}', [App\Http\Controllers\Admin\InventoryTransferController::class, 'getItemDetails']);

    // Admin Reports
    Route::get('/admin/reports', [App\Http\Controllers\Admin\ReportController::class, 'index'])
        ->name('admin.reports.index');
    Route::get('/admin/reports/export', [App\Http\Controllers\Admin\ReportController::class, 'export'])
        ->name('admin.reports.export');
    Route::get('/admin/reports/export-word', [App\Http\Controllers\Admin\ReportController::class, 'exportWord'])
        ->name('admin.reports.export-word');

    // Admin Lab Management
    Route::resource('/admin/labs', App\Http\Controllers\Admin\LabController::class)
        ->names('admin.labs')
        ->except(['show']);
    Route::post('/admin/labs/{lab}/toggle-status', [App\Http\Controllers\Admin\LabController::class, 'toggleStatus'])
        ->name('admin.labs.toggle-status');

    // Admin Feedback Management
    Route::get('/admin/feedbacks', [FeedbackController::class, 'index'])->name('admin.feedbacks.index');
    Route::put('/admin/feedbacks/{id}', [FeedbackController::class, 'update'])->name('admin.feedbacks.update');
    
    // Admin Asset Borrowing Management Actions
    Route::get('/admin/asset-borrowings', [AssetBorrowingController::class, 'index'])
        ->name('admin.asset-borrowings.index');
    Route::get('/admin/asset-borrowings/{id}', [AssetBorrowingController::class, 'show'])
        ->name('admin.asset-borrowings.show');
    Route::post('/admin/asset-borrowings/{id}/first-party', [AssetBorrowingController::class, 'updateFirstParty'])
        ->name('admin.asset-borrowings.update-first-party');
    Route::get('/admin/asset-borrowings/{id}/download', [AssetBorrowingController::class, 'downloadDocument'])
        ->name('admin.asset-borrowings.download');
    Route::get('/admin/asset-borrowings/{id}/preview', [AssetBorrowingController::class, 'previewDocument'])
        ->name('admin.asset-borrowings.preview');
    Route::post('/admin/asset-borrowings/{id}/approve', [AssetBorrowingController::class, 'approve'])
        ->name('admin.asset-borrowings.approve');
    Route::post('/admin/asset-borrowings/{id}/reject', [AssetBorrowingController::class, 'reject'])
        ->name('admin.asset-borrowings.reject');
    Route::post('/admin/asset-borrowings/{id}/handout', [AssetBorrowingController::class, 'handout'])
        ->name('admin.asset-borrowings.handout');
    Route::get('/admin/asset-borrowings/{id}/available-units', [AssetBorrowingController::class, 'getAvailableUnits'])
        ->name('admin.asset-borrowings.available-units');
    Route::get('/admin/asset-borrowings/{id}/borrowed-units', [AssetBorrowingController::class, 'getBorrowedUnits'])
        ->name('admin.asset-borrowings.borrowed-units');
    Route::post('/admin/asset-borrowings/{id}/receive', [AssetBorrowingController::class, 'receive'])
        ->name('admin.asset-borrowings.receive');
    Route::post('/admin/asset-borrowings/{id}/confirm-replacement', [AssetBorrowingController::class, 'confirmReplacement'])
        ->name('admin.asset-borrowings.confirm-replacement');
});

// Super Admin Only Routes
Route::middleware(['auth', 'super_admin'])->group(function () {
    Route::get('/admin/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/create', [App\Http\Controllers\Admin\UserController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('admin.users.update');
    Route::put('/admin/users/{user}/reset-password', [App\Http\Controllers\Admin\UserController::class, 'resetPassword'])->name('admin.users.reset-password');
    Route::delete('/admin/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('admin.users.destroy');
});
