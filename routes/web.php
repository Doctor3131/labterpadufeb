<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AdminController;

// Public Routes
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Booking Routes (Public - No Authentication Required)
Route::get('/booking', [BookingController::class, 'create'])->name('booking.create');
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
Route::get('/booking/success/{token}', [BookingController::class, 'success'])->name('booking.success');
Route::post('/booking/available-labs', [BookingController::class, 'getAvailableLabs'])->name('booking.available-labs');

// Lab Availability API
Route::get('/api/labs/available', [App\Http\Controllers\LabController::class, 'checkAvailability'])->name('api.labs.available');

// PDF Print (for re-download)
Route::get('/booking/print/{token}', [BookingController::class, 'print'])->name('booking.print');

// Schedule Routes (Public)
Route::get('/schedules', function () {
    return view('schedules.index');
})->name('schedules.index');
Route::get('/schedules/week', [App\Http\Controllers\ScheduleController::class, 'getWeekSchedules'])->name('schedules.week');

// TV Display Mode (Fullscreen for TV/Monitor)
Route::get('/display', [App\Http\Controllers\ScheduleController::class, 'display'])->name('schedules.display');

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes (Admin/Super Admin only)
Route::middleware('auth')->group(function () {
    // Redirect /dashboard to admin dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Admin Booking Management
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/bookings/{id}', [AdminController::class, 'show'])->name('admin.booking.show');
    Route::post('/admin/bookings/{id}/approve', [AdminController::class, 'approve'])->name('admin.booking.approve');
    Route::post('/admin/bookings/{id}/reject', [AdminController::class, 'reject'])->name('admin.booking.reject');
    
    // Admin Schedule CRUD
    Route::resource('/admin/schedules', App\Http\Controllers\Admin\ScheduleController::class)
        ->names('admin.schedules')
        ->except(['show']);
});

// Super Admin Only Routes
Route::middleware(['auth', 'super_admin'])->group(function () {
    Route::get('/admin/users/create', [AuthController::class, 'showRegister'])->name('admin.users.create');
    Route::post('/admin/users', [AuthController::class, 'register'])->name('admin.users.store');
});
