<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\BpsRequestController;
use App\Http\Controllers\RefinitivRequestController;
use App\Http\Controllers\PersonalBorrowingController;
use App\Http\Controllers\BloombergRequestController;

// Public Routes
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Data Borrowing Selection Page
Route::get('/data', function () {
    return view('data.index');
})->name('data.index');

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

// Personal Borrowing NIM Validation (AJAX)
Route::post('/personal-borrowing/validate-nim', [PersonalBorrowingController::class, 'validateNim'])
    ->middleware('throttle:60,1')
    ->name('personal-borrowing.validate-nim');

// PDF Print (for re-download)
Route::get('/booking/print/{token}', [BookingController::class, 'print'])->name('booking.print');

// BPS Data Request Routes (Public)
Route::get('/bps', [BpsRequestController::class, 'create'])->name('bps.create');
Route::post('/bps', [BpsRequestController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('bps.store');
Route::get('/bps/success/{token}', [BpsRequestController::class, 'success'])->name('bps.success');
Route::get('/api/bps/sub-data/{master}', [BpsRequestController::class, 'getSubData'])->name('api.bps.sub-data');

// Refinitiv Data Request Routes (Public)
Route::get('/refinitiv', [RefinitivRequestController::class, 'create'])->name('refinitiv.create');
Route::post('/refinitiv', [RefinitivRequestController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('refinitiv.store');
Route::get('/refinitiv/success/{token}', [RefinitivRequestController::class, 'success'])->name('refinitiv.success');

// Bloomberg Routes (Public)
Route::get('/bloomberg', function () { return view('bloomberg.index'); })->name('bloomberg.index');
Route::get('/bloomberg/reservasi', [BloombergRequestController::class, 'create'])->name('bloomberg.create');
Route::get('/bloomberg/walk-in', [BloombergRequestController::class, 'createWalkIn'])->name('bloomberg.walk-in');
Route::post('/bloomberg', [BloombergRequestController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('bloomberg.store');
Route::get('/bloomberg/success/{token}', [BloombergRequestController::class, 'success'])->name('bloomberg.success');
Route::get('/api/bloomberg/capacity', [BloombergRequestController::class, 'checkCapacity'])
    ->middleware('throttle:60,1')
    ->name('api.bloomberg.capacity');

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
    Route::get('/admin/lab-bookings', [AdminController::class, 'labBookings'])->name('admin.lab.bookings');
    Route::get('/admin/bookings/{id}', [AdminController::class, 'show'])->name('admin.booking.show');
    Route::post('/admin/bookings/{id}/approve', [AdminController::class, 'approve'])->name('admin.booking.approve');
    Route::post('/admin/bookings/{id}/reject', [AdminController::class, 'reject'])->name('admin.booking.reject');
    
    // Admin Schedule CRUD
    Route::post('/admin/schedules/available-labs', [App\Http\Controllers\Admin\ScheduleController::class, 'getAvailableLabs'])
        ->name('admin.schedules.available-labs');
    Route::get('/admin/schedules/{schedule}/print', [App\Http\Controllers\Admin\ScheduleController::class, 'print'])
        ->name('admin.schedules.print');
    Route::delete('/admin/schedules/{schedule}/ktm', [App\Http\Controllers\Admin\ScheduleController::class, 'deleteKtm'])
        ->name('admin.schedules.delete-ktm');
    Route::resource('/admin/schedules', App\Http\Controllers\Admin\ScheduleController::class)
        ->names('admin.schedules')
        ->except(['show']);

    // Admin Inventory Management
    Route::resource('/admin/inventories', App\Http\Controllers\Admin\InventoryController::class)
        ->names('admin.inventories')
        ->except(['show']);

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

    // Admin BPS Data Management
    Route::get('/admin/bps/requests', [App\Http\Controllers\Admin\BpsRequestController::class, 'index'])->name('admin.bps.requests.index');
    Route::get('/admin/bps/requests/{request}', [App\Http\Controllers\Admin\BpsRequestController::class, 'show'])->name('admin.bps.requests.show');
    Route::put('/admin/bps/requests/{request}/complete', [App\Http\Controllers\Admin\BpsRequestController::class, 'markCompleted'])->name('admin.bps.requests.complete');

    // Admin BPS Master Data CRUD
    Route::resource('/admin/bps/master-data', App\Http\Controllers\Admin\BpsMasterDataController::class)
        ->names('admin.bps.master-data')
        ->except(['show']);
    Route::post('/admin/bps/master-data/{masterDatum}/toggle-status', [App\Http\Controllers\Admin\BpsMasterDataController::class, 'toggleStatus'])
        ->name('admin.bps.master-data.toggle-status');

    // Admin BPS Sub Data CRUD
    Route::get('/admin/bps/master/{master}/sub-data', [App\Http\Controllers\Admin\BpsSubDataController::class, 'index'])->name('admin.bps.sub-data.index');
    Route::get('/admin/bps/master/{master}/sub-data/create', [App\Http\Controllers\Admin\BpsSubDataController::class, 'create'])->name('admin.bps.sub-data.create');
    Route::post('/admin/bps/master/{master}/sub-data', [App\Http\Controllers\Admin\BpsSubDataController::class, 'store'])->name('admin.bps.sub-data.store');
    Route::get('/admin/bps/master/{master}/sub-data/{subDatum}/edit', [App\Http\Controllers\Admin\BpsSubDataController::class, 'edit'])->name('admin.bps.sub-data.edit');
    Route::put('/admin/bps/master/{master}/sub-data/{subDatum}', [App\Http\Controllers\Admin\BpsSubDataController::class, 'update'])->name('admin.bps.sub-data.update');
    Route::delete('/admin/bps/master/{master}/sub-data/{subDatum}', [App\Http\Controllers\Admin\BpsSubDataController::class, 'destroy'])->name('admin.bps.sub-data.destroy');
    Route::post('/admin/bps/master/{master}/sub-data/{subDatum}/toggle-status', [App\Http\Controllers\Admin\BpsSubDataController::class, 'toggleStatus'])->name('admin.bps.sub-data.toggle-status');

    // Admin Refinitiv Data Management
    Route::get('/admin/refinitiv', [App\Http\Controllers\Admin\RefinitivRequestController::class, 'index'])->name('admin.refinitiv.index');
    Route::get('/admin/refinitiv/{request}', [App\Http\Controllers\Admin\RefinitivRequestController::class, 'show'])->name('admin.refinitiv.show');
    Route::put('/admin/refinitiv/{request}/hadir', [App\Http\Controllers\Admin\RefinitivRequestController::class, 'markHadir'])->name('admin.refinitiv.hadir');
    Route::put('/admin/refinitiv/{request}/tidak-hadir', [App\Http\Controllers\Admin\RefinitivRequestController::class, 'markTidakHadir'])->name('admin.refinitiv.tidak-hadir');
    Route::put('/admin/refinitiv/{request}/reset', [App\Http\Controllers\Admin\RefinitivRequestController::class, 'resetStatus'])->name('admin.refinitiv.reset');

    // Admin Bloomberg Reservation Management
    Route::get('/admin/bloomberg', [App\Http\Controllers\Admin\BloombergRequestController::class, 'index'])->name('admin.bloomberg.index');
    Route::get('/admin/bloomberg/blocked-dates', [App\Http\Controllers\Admin\BloombergRequestController::class, 'blockedDates'])->name('admin.bloomberg.blocked-dates');
    Route::post('/admin/bloomberg/blocked-dates', [App\Http\Controllers\Admin\BloombergRequestController::class, 'addBlockedDate'])->name('admin.bloomberg.blocked-dates.store');
    Route::delete('/admin/bloomberg/blocked-dates/{blockedDate}', [App\Http\Controllers\Admin\BloombergRequestController::class, 'removeBlockedDate'])->name('admin.bloomberg.blocked-dates.destroy');
    Route::get('/admin/bloomberg/settings', [App\Http\Controllers\Admin\BloombergRequestController::class, 'settings'])->name('admin.bloomberg.settings');
    Route::put('/admin/bloomberg/settings', [App\Http\Controllers\Admin\BloombergRequestController::class, 'updateSettings'])->name('admin.bloomberg.settings.update');
    Route::get('/admin/bloomberg/{request}', [App\Http\Controllers\Admin\BloombergRequestController::class, 'show'])->name('admin.bloomberg.show');

    // Admin Announcements
    Route::resource('/admin/announcements', App\Http\Controllers\Admin\AnnouncementController::class)
        ->names('admin.announcements')
        ->except(['show', 'create', 'edit']);
    Route::post('/admin/announcements/{announcement}/toggle-active', [App\Http\Controllers\Admin\AnnouncementController::class, 'toggleActive'])
        ->name('admin.announcements.toggle-active');

    // Admin Personal Borrowings Report (read-only view, filtered from bookings)
    Route::get('/admin/personal-borrowings', [App\Http\Controllers\Admin\PersonalBorrowingController::class, 'index'])
        ->name('admin.personal-borrowings.index');
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
