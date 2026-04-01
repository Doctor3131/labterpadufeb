<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\AssetBorrowingController;
use App\Http\Controllers\BpsRequestController;
use App\Http\Controllers\RefinitivRequestController;

// Public Routes
Route::get("/", [LandingController::class, "index"])->name("landing");

// Data Borrowing Selection Page
Route::get("/data", function () {
    return view("data.index");
})->name("data.index");

// BPS Data Request Routes (Public - rate limited)
Route::get("/bps", [BpsRequestController::class, "create"])->name("bps.create");
Route::post("/bps", [BpsRequestController::class, "store"])
    ->middleware("throttle:10,1")
    ->name("bps.store");
Route::get("/bps/success/{token}", [
    BpsRequestController::class,
    "success",
])->name("bps.success");
Route::get("/api/bps/sub-data/{master}", [
    BpsRequestController::class,
    "getSubData",
])->name("api.bps.sub-data");

// Refinitiv Data Request Routes (Public - rate limited)
Route::get("/refinitiv", [RefinitivRequestController::class, "create"])->name(
    "refinitiv.create",
);
Route::post("/refinitiv", [RefinitivRequestController::class, "store"])
    ->middleware("throttle:10,1")
    ->name("refinitiv.store");
Route::get("/refinitiv/success/{token}", [
    RefinitivRequestController::class,
    "success",
])->name("refinitiv.success");

// Bloomberg Reservation Routes (Public)
Route::get('/bloomberg', fn() => view('bloomberg.index'))->name('bloomberg.index');
Route::get('/bloomberg/reservasi', [App\Http\Controllers\BloombergRequestController::class, 'create'])->name('bloomberg.create');
Route::post('/bloomberg/reservasi', [App\Http\Controllers\BloombergRequestController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('bloomberg.store');
Route::get('/bloomberg/walkin', [App\Http\Controllers\BloombergRequestController::class, 'createWalkIn'])->name('bloomberg.walkin');
Route::get('/bloomberg/capacity', [App\Http\Controllers\BloombergRequestController::class, 'checkCapacity'])->name('bloomberg.capacity');
Route::get('/bloomberg/success/{token}', [App\Http\Controllers\BloombergRequestController::class, 'success'])->name('bloomberg.success');

// Personal Borrowing Routes (Public - NIM Validation)
Route::post("/personal-borrowing/validate-nim", [
    App\Http\Controllers\PersonalBorrowingController::class,
    "validateNim",
])
    ->middleware("throttle:60,1")
    ->name("personal-borrowing.validate-nim");

// Feedback Routes (Public)
Route::post("/feedback", [FeedbackController::class, "store"])
    ->middleware("throttle:5,1") // Max 5 submissions per minute
    ->name("feedback.store");

// Booking Routes (Public - No Authentication Required)
// Rate limited to prevent spam submissions
Route::get("/booking", [BookingController::class, "create"])->name(
    "booking.create",
);
Route::post("/booking", [BookingController::class, "store"])
    ->middleware("throttle:10,1") // Max 10 submissions per minute
    ->name("booking.store");
Route::get("/booking/success/{token}", [
    BookingController::class,
    "success",
])->name("booking.success");
Route::post("/booking/available-labs", [
    BookingController::class,
    "getAvailableLabs",
])
    ->middleware("throttle:60,1") // Max 60 requests per minute (for AJAX)
    ->name("booking.available-labs");

// Lab Availability API (rate limited)
Route::get("/api/labs/available", [
    App\Http\Controllers\LabController::class,
    "checkAvailability",
])
    ->middleware("throttle:60,1")
    ->name("api.labs.available");

// PDF Print (for re-download)
Route::get("/booking/print/{token}", [BookingController::class, "print"])->name(
    "booking.print",
);

// BPS Data Request Routes (Public)
Route::get("/bps", [BpsRequestController::class, "create"])->name("bps.create");
Route::post("/bps", [BpsRequestController::class, "store"])
    ->middleware("throttle:10,1")
    ->name("bps.store");
Route::get("/bps/success/{token}", [
    BpsRequestController::class,
    "success",
])->name("bps.success");
Route::get("/api/bps/sub-data/{master}", [
    BpsRequestController::class,
    "getSubData",
])->name("api.bps.sub-data");

// Refinitiv Data Request Routes (Public)
Route::get("/refinitiv", [RefinitivRequestController::class, "create"])->name(
    "refinitiv.create",
);
Route::post("/refinitiv", [RefinitivRequestController::class, "store"])
    ->middleware("throttle:10,1")
    ->name("refinitiv.store");
Route::get("/refinitiv/success/{token}", [
    RefinitivRequestController::class,
    "success",
])->name("refinitiv.success");

// Asset Borrowing Routes (Public - No Authentication Required)
Route::get("/asset-borrowing", [
    AssetBorrowingController::class,
    "create",
])->name("asset-borrowing.create");
Route::post("/asset-borrowing", [AssetBorrowingController::class, "store"])
    ->middleware("throttle:10,1") // Max 10 submissions per minute
    ->name("asset-borrowing.store");
Route::get("/asset-borrowing/success/{token}", [
    AssetBorrowingController::class,
    "success",
])->name("asset-borrowing.success");
Route::get("/asset-borrowing/available-assets", [
    AssetBorrowingController::class,
    "getAvailableAssets",
])
    ->middleware("throttle:60,1")
    ->name("asset-borrowing.available-assets");

// Schedule Routes (Public)
Route::get("/schedules", function () {
    return view("schedules.index");
})->name("schedules.index");
Route::get("/schedules/week", [
    App\Http\Controllers\ScheduleController::class,
    "getWeekSchedules",
])->name("schedules.week");

// TV Display Mode (Fullscreen for TV/Monitor)
Route::get("/display", [
    App\Http\Controllers\ScheduleController::class,
    "display",
])->name("schedules.display");

// Auth Routes (rate limited to prevent brute force)
Route::get("/login", [AuthController::class, "showLogin"])->name("login");
Route::post("/login", [AuthController::class, "login"])->middleware(
    "throttle:5,1",
); // Max 5 login attempts per minute

// Protected Routes (Admin/Super Admin only)
// Uses 'admin' middleware to explicitly check role, not just authentication
Route::middleware(["auth", "admin"])->group(function () {
    // Secure file access (serves files from storage through authenticated route)
    Route::get("/admin/files/{path}", [
        App\Http\Controllers\SecureFileController::class,
        "show",
    ])
        ->where("path", ".*")
        ->name("admin.secure-file");

    // Redirect /dashboard to admin dashboard
    Route::get("/dashboard", [DashboardController::class, "index"])->name(
        "dashboard",
    );
    Route::post("/logout", [AuthController::class, "logout"])->name("logout");

    // Profile / Account Management
    Route::get("/profile", [
        App\Http\Controllers\ProfileController::class,
        "show",
    ])->name("profile.show");
    Route::put("/profile", [
        App\Http\Controllers\ProfileController::class,
        "updateProfile",
    ])->name("profile.update");
    Route::put("/profile/password", [
        App\Http\Controllers\ProfileController::class,
        "updatePassword",
    ])->name("profile.password");

    // Admin Booking Management
    Route::get("/admin/dashboard", [AdminController::class, "dashboard"])->name(
        "admin.dashboard",
    );
    Route::get("/admin/bookings/{id}", [AdminController::class, "show"])->name(
        "admin.booking.show",
    );
    Route::post("/admin/bookings/{id}/approve", [
        AdminController::class,
        "approve",
    ])->name("admin.booking.approve");
    Route::post("/admin/bookings/{id}/reject", [
        AdminController::class,
        "reject",
    ])->name("admin.booking.reject");

    // Admin Schedule CRUD
    Route::post("/admin/schedules/available-labs", [
        App\Http\Controllers\Admin\ScheduleController::class,
        "getAvailableLabs",
    ])->name("admin.schedules.available-labs");
    Route::resource(
        "/admin/schedules",
        App\Http\Controllers\Admin\ScheduleController::class,
    )
        ->names("admin.schedules")
        ->except(["show"]);
    Route::get("/admin/schedules/{schedule}/print", [
        App\Http\Controllers\Admin\ScheduleController::class,
        "print",
    ])->name("admin.schedules.print");
    Route::delete("/admin/schedules/{schedule}/ktm", [
        App\Http\Controllers\Admin\ScheduleController::class,
        "deleteKtm",
    ])->name("admin.schedules.delete-ktm");

    // Admin Inventory Management (Global overview)
    Route::get("/admin/inventory", [
        App\Http\Controllers\Admin\LabInventoryController::class,
        "globalIndex",
    ])->name("admin.inventory.index");

    // Admin Inventory Management (Lab-based)
    Route::get("/admin/labs/{lab}/inventory", [
        App\Http\Controllers\Admin\LabInventoryController::class,
        "index",
    ])->name("admin.labs.inventory");
    Route::get("/admin/labs/{lab}/inventory/ledger", [
        App\Http\Controllers\Admin\LabInventoryController::class,
        "ledger",
    ])->name("admin.labs.inventory.ledger");
    Route::get("/admin/labs/{lab}/inventory/create", [
        App\Http\Controllers\Admin\LabInventoryController::class,
        "create",
    ])->name("admin.labs.inventory.create");
    Route::post("/admin/labs/{lab}/inventory", [
        App\Http\Controllers\Admin\LabInventoryController::class,
        "store",
    ])->name("admin.labs.inventory.store");
    Route::get("/admin/labs/{lab}/inventory/{item}/units", [
        App\Http\Controllers\Admin\LabInventoryController::class,
        "showUnits",
    ])->name("admin.labs.inventory.units");
    Route::get("/admin/labs/{lab}/inventory/{item}/balances", [
        App\Http\Controllers\Admin\LabInventoryController::class,
        "showBalances",
    ])->name("admin.labs.inventory.balances");

    // Lab Inventory Logs (Mutasi Barang - Global)
    Route::get("/admin/inventory-logs", [
        App\Http\Controllers\Admin\InventoryLogController::class,
        "index",
    ])->name("admin.inventory.logs.index");
    Route::get("/admin/inventory-logs/create", [
        App\Http\Controllers\Admin\InventoryLogController::class,
        "create",
    ])->name("admin.inventory.logs.create");
    Route::post("/admin/inventory-logs", [
        App\Http\Controllers\Admin\InventoryLogController::class,
        "store",
    ])->name("admin.inventory.logs.store");
    Route::get("/admin/inventory-logs/{log}/edit", [
        App\Http\Controllers\Admin\InventoryLogController::class,
        "edit",
    ])->name("admin.inventory.logs.edit");
    Route::put("/admin/inventory-logs/{log}", [
        App\Http\Controllers\Admin\InventoryLogController::class,
        "update",
    ])->name("admin.inventory.logs.update");
    Route::patch("/admin/inventory-logs/{log}/toggle-flow", [
        App\Http\Controllers\Admin\InventoryLogController::class,
        "toggleFlow",
    ])->name("admin.inventory.logs.toggle-flow");
    Route::delete("/admin/inventory-logs/{log}", [
        App\Http\Controllers\Admin\InventoryLogController::class,
        "destroy",
    ])->name("admin.inventory.logs.destroy");

    Route::post("/admin/inventory/bulk-update-condition", [
        App\Http\Controllers\Admin\LabInventoryController::class,
        "bulkUpdateCondition",
    ])->name("admin.inventory.bulk-condition");
    Route::post("/admin/labs/{lab}/inventory/transfer-balance", [
        App\Http\Controllers\Admin\LabInventoryController::class,
        "transferBalance",
    ])->name("admin.labs.inventory.transfer");
    Route::get("/admin/items/{item}/batches", [
        App\Http\Controllers\Admin\LabInventoryController::class,
        "getBatches",
    ])->name("admin.items.batches");

    // Delete inventory routes
    Route::delete("/admin/labs/{lab}/inventory/{item}", [
        App\Http\Controllers\Admin\LabInventoryController::class,
        "destroyItem",
    ])->name("admin.labs.inventory.destroy");
    Route::delete("/admin/inventory/units/{unit}", [
        App\Http\Controllers\Admin\LabInventoryController::class,
        "destroyUnit",
    ])->name("admin.inventory.units.destroy");
    Route::post("/admin/inventory/bulk-delete-units", [
        App\Http\Controllers\Admin\LabInventoryController::class,
        "bulkDestroyUnits",
    ])->name("admin.inventory.bulk-delete");
    Route::patch("/admin/inventory/units/{unit}/university-code", [
        App\Http\Controllers\Admin\LabInventoryController::class,
        "updateUniversityCode",
    ])->name("admin.inventory.units.update-university-code");
    Route::patch("/admin/inventory/units/{unit}/asset-tag", [
        App\Http\Controllers\Admin\LabInventoryController::class,
        "updateAssetTag",
    ])->name("admin.inventory.units.update-asset-tag");
    Route::patch("/admin/inventory/units/{unit}/notes", [
        App\Http\Controllers\Admin\LabInventoryController::class,
        "updateNotes",
    ])->name("admin.inventory.units.update-notes");
    Route::patch("/admin/inventory/balance/{balance}/university-code", [
        App\Http\Controllers\Admin\LabInventoryController::class,
        "updateUniversityCodeBalance",
    ])->name("admin.inventory.balance.update-university-code");
    Route::patch("/admin/inventory/balance/{balance}/individual-code", [
        App\Http\Controllers\Admin\LabInventoryController::class,
        "updateAggregateItemCode",
    ])->name("admin.inventory.balance.update-aggregate-item-code");

    // Transfer inventory (e.g. to Gudang) routes
    Route::post("/admin/inventory/bulk-transfer-units", [
        App\Http\Controllers\Admin\LabInventoryController::class,
        "bulkTransferUnits",
    ])->name("admin.inventory.bulk-transfer");
    Route::post("/admin/labs/{lab}/inventory/transfer-aggregate", [
        App\Http\Controllers\Admin\LabInventoryController::class,
        "transferAggregate",
    ])->name("admin.labs.inventory.transfer-aggregate");

    // Unified Transfer Form
    Route::get("/admin/inventory/transfer", [
        App\Http\Controllers\Admin\InventoryTransferController::class,
        "create",
    ])->name("admin.inventory.transfer.create");
    Route::post("/admin/inventory/transfer", [
        App\Http\Controllers\Admin\InventoryTransferController::class,
        "store",
    ])->name("admin.inventory.transfer.store");

    // Item Master Data Route
    Route::get("/admin/items/{item}", [
        App\Http\Controllers\Admin\ItemController::class,
        "show",
    ])->name("admin.items.show");
    Route::delete("/admin/items/{item}", [
        App\Http\Controllers\Admin\ItemController::class,
        "destroy",
    ])->name("admin.items.destroy");
    Route::patch("/admin/batches/{batch}/brand", [
        App\Http\Controllers\Admin\ItemController::class,
        "updateBatchBrand",
    ])->name("admin.batches.updateBrand");
    Route::patch("/admin/units/bulk-brand", [
        App\Http\Controllers\Admin\ItemController::class,
        "bulkUpdateUnitBrand",
    ])->name("admin.units.bulkUpdateBrand");
    Route::patch("/admin/units/{unit}/brand", [
        App\Http\Controllers\Admin\ItemController::class,
        "updateUnitBrand",
    ])->name("admin.units.updateBrand");
    Route::patch("/admin/balances/{balance}/brand", [
        App\Http\Controllers\Admin\ItemController::class,
        "updateBalanceBrand",
    ])->name("admin.balances.updateBrand");
    // API for Unified Transfer Form
    Route::get("/admin/api/inventory/{lab}/items", [
        App\Http\Controllers\Admin\InventoryTransferController::class,
        "getItems",
    ]);
    Route::get("/admin/api/inventory/{lab}/items/{item}", [
        App\Http\Controllers\Admin\InventoryTransferController::class,
        "getItemDetails",
    ]);

    // External Transfer (Gudang → Eksternal)
    Route::get("/admin/external-transfers", [
        App\Http\Controllers\Admin\ExternalTransferController::class,
        "index",
    ])->name("admin.external-transfers.index");
    Route::get("/admin/external-transfers/create", [
        App\Http\Controllers\Admin\ExternalTransferController::class,
        "create",
    ])->name("admin.external-transfers.create");
    Route::post("/admin/external-transfers", [
        App\Http\Controllers\Admin\ExternalTransferController::class,
        "store",
    ])->name("admin.external-transfers.store");
    Route::patch("/admin/external-transfers/{externalTransfer}/toggle-status", [
        App\Http\Controllers\Admin\ExternalTransferController::class,
        "toggleStatus",
    ])->name("admin.external-transfers.toggle-status");
    Route::get("/admin/external-transfers/{externalTransfer}", [
        App\Http\Controllers\Admin\ExternalTransferController::class,
        "show",
    ])->name("admin.external-transfers.show");
    // API for External Transfer Form
    Route::get("/admin/api/external-transfers/items", [
        App\Http\Controllers\Admin\ExternalTransferController::class,
        "getGudangItems",
    ]);
    Route::get("/admin/api/external-transfers/items/{item}", [
        App\Http\Controllers\Admin\ExternalTransferController::class,
        "getItemDetails",
    ]);

    // Admin BPS Data Management
    Route::get("/admin/bps/requests", [
        App\Http\Controllers\Admin\BpsRequestController::class,
        "index",
    ])->name("admin.bps.requests.index");
    Route::get("/admin/bps/requests/{request}", [
        App\Http\Controllers\Admin\BpsRequestController::class,
        "show",
    ])->name("admin.bps.requests.show");
    Route::put("/admin/bps/requests/{request}/complete", [
        App\Http\Controllers\Admin\BpsRequestController::class,
        "markCompleted",
    ])->name("admin.bps.requests.complete");

    // Admin BPS Master Data CRUD
    Route::resource(
        "/admin/bps/master-data",
        App\Http\Controllers\Admin\BpsMasterDataController::class,
    )
        ->names("admin.bps.master-data")
        ->except(["show"]);
    Route::post("/admin/bps/master-data/{masterDatum}/toggle-status", [
        App\Http\Controllers\Admin\BpsMasterDataController::class,
        "toggleStatus",
    ])->name("admin.bps.master-data.toggle-status");

    // Admin BPS Sub Data CRUD
    Route::get("/admin/bps/master/{master}/sub-data", [
        App\Http\Controllers\Admin\BpsSubDataController::class,
        "index",
    ])->name("admin.bps.sub-data.index");
    Route::get("/admin/bps/master/{master}/sub-data/create", [
        App\Http\Controllers\Admin\BpsSubDataController::class,
        "create",
    ])->name("admin.bps.sub-data.create");
    Route::post("/admin/bps/master/{master}/sub-data", [
        App\Http\Controllers\Admin\BpsSubDataController::class,
        "store",
    ])->name("admin.bps.sub-data.store");
    Route::get("/admin/bps/master/{master}/sub-data/{subDatum}/edit", [
        App\Http\Controllers\Admin\BpsSubDataController::class,
        "edit",
    ])->name("admin.bps.sub-data.edit");
    Route::put("/admin/bps/master/{master}/sub-data/{subDatum}", [
        App\Http\Controllers\Admin\BpsSubDataController::class,
        "update",
    ])->name("admin.bps.sub-data.update");
    Route::delete("/admin/bps/master/{master}/sub-data/{subDatum}", [
        App\Http\Controllers\Admin\BpsSubDataController::class,
        "destroy",
    ])->name("admin.bps.sub-data.destroy");
    Route::post(
        "/admin/bps/master/{master}/sub-data/{subDatum}/toggle-status",
        [
            App\Http\Controllers\Admin\BpsSubDataController::class,
            "toggleStatus",
        ],
    )->name("admin.bps.sub-data.toggle-status");

    // Admin Refinitiv Data Management
    Route::get("/admin/refinitiv", [
        App\Http\Controllers\Admin\RefinitivRequestController::class,
        "index",
    ])->name("admin.refinitiv.index");
    Route::get("/admin/refinitiv/{request}", [
        App\Http\Controllers\Admin\RefinitivRequestController::class,
        "show",
    ])->name("admin.refinitiv.show");
    Route::put("/admin/refinitiv/{request}/hadir", [
        App\Http\Controllers\Admin\RefinitivRequestController::class,
        "markHadir",
    ])->name("admin.refinitiv.hadir");
    Route::put("/admin/refinitiv/{request}/tidak-hadir", [
        App\Http\Controllers\Admin\RefinitivRequestController::class,
        "markTidakHadir",
    ])->name("admin.refinitiv.tidak-hadir");
    Route::put("/admin/refinitiv/{request}/reset", [
        App\Http\Controllers\Admin\RefinitivRequestController::class,
        "resetStatus",
    ])->name("admin.refinitiv.reset");

    // Admin Reports
    Route::get("/admin/reports", [
        App\Http\Controllers\Admin\ReportController::class,
        "index",
    ])->name("admin.reports.index");
    Route::get("/admin/reports/export", [
        App\Http\Controllers\Admin\ReportController::class,
        "export",
    ])->name("admin.reports.export");
    Route::get("/admin/reports/export-word", [
        App\Http\Controllers\Admin\ReportController::class,
        "exportWord",
    ])->name("admin.reports.export-word");

    // Admin Lab Management
    Route::resource(
        "/admin/labs",
        App\Http\Controllers\Admin\LabController::class,
    )
        ->names("admin.labs")
        ->except(["show"]);
    Route::post("/admin/labs/{lab}/toggle-status", [
        App\Http\Controllers\Admin\LabController::class,
        "toggleStatus",
    ])->name("admin.labs.toggle-status");

    // Admin Lab Bookings (dedicated page)
    Route::get("/admin/lab-bookings", [
        AdminController::class,
        "labBookings",
    ])->name("admin.lab.bookings");

    // Admin Bloomberg Reservation Management
    Route::get("/admin/bloomberg", [
        App\Http\Controllers\Admin\BloombergRequestController::class,
        "index",
    ])->name("admin.bloomberg.index");
    Route::get("/admin/bloomberg/blocked-dates", [
        App\Http\Controllers\Admin\BloombergRequestController::class,
        "blockedDates",
    ])->name("admin.bloomberg.blocked-dates");
    Route::post("/admin/bloomberg/blocked-dates", [
        App\Http\Controllers\Admin\BloombergRequestController::class,
        "addBlockedDate",
    ])->name("admin.bloomberg.blocked-dates.store");
    Route::delete("/admin/bloomberg/blocked-dates/{blockedDate}", [
        App\Http\Controllers\Admin\BloombergRequestController::class,
        "removeBlockedDate",
    ])->name("admin.bloomberg.blocked-dates.destroy");
    Route::get("/admin/bloomberg/settings", [
        App\Http\Controllers\Admin\BloombergRequestController::class,
        "settings",
    ])->name("admin.bloomberg.settings");
    Route::put("/admin/bloomberg/settings", [
        App\Http\Controllers\Admin\BloombergRequestController::class,
        "updateSettings",
    ])->name("admin.bloomberg.settings.update");
    Route::get("/admin/bloomberg/{request}", [
        App\Http\Controllers\Admin\BloombergRequestController::class,
        "show",
    ])->name("admin.bloomberg.show");

    // Admin Personal Borrowings
    Route::get("/admin/personal-borrowings", [
        App\Http\Controllers\Admin\PersonalBorrowingController::class,
        "index",
    ])->name("admin.personal-borrowings.index");

    // Admin Announcements
    Route::resource(
        "/admin/announcements",
        App\Http\Controllers\Admin\AnnouncementController::class,
    )
        ->names("admin.announcements")
        ->except(["show"]);
    Route::post("/admin/announcements/{announcement}/toggle-active", [
        App\Http\Controllers\Admin\AnnouncementController::class,
        "toggleActive",
    ])->name("admin.announcements.toggle-active");

    // Admin Feedback Management
    Route::get("/admin/feedbacks", [FeedbackController::class, "index"])->name(
        "admin.feedbacks.index",
    );
    Route::put("/admin/feedbacks/{id}", [
        FeedbackController::class,
        "update",
    ])->name("admin.feedbacks.update");

    // Admin Asset Borrowing Management Actions
    Route::get("/admin/asset-borrowings", [
        AssetBorrowingController::class,
        "index",
    ])->name("admin.asset-borrowings.index");
    Route::get("/admin/asset-borrowings/{id}", [
        AssetBorrowingController::class,
        "show",
    ])->name("admin.asset-borrowings.show");
    Route::post("/admin/asset-borrowings/{id}/first-party", [
        AssetBorrowingController::class,
        "updateFirstParty",
    ])->name("admin.asset-borrowings.update-first-party");
    Route::get("/admin/asset-borrowings/{id}/download", [
        AssetBorrowingController::class,
        "downloadDocument",
    ])->name("admin.asset-borrowings.download");
    Route::get("/admin/asset-borrowings/{id}/preview", [
        AssetBorrowingController::class,
        "previewDocument",
    ])->name("admin.asset-borrowings.preview");
    Route::post("/admin/asset-borrowings/{id}/approve", [
        AssetBorrowingController::class,
        "approve",
    ])->name("admin.asset-borrowings.approve");
    Route::post("/admin/asset-borrowings/{id}/reject", [
        AssetBorrowingController::class,
        "reject",
    ])->name("admin.asset-borrowings.reject");
    Route::post("/admin/asset-borrowings/{id}/handout", [
        AssetBorrowingController::class,
        "handout",
    ])->name("admin.asset-borrowings.handout");
    Route::get("/admin/asset-borrowings/{id}/available-units", [
        AssetBorrowingController::class,
        "getAvailableUnits",
    ])->name("admin.asset-borrowings.available-units");
    Route::get("/admin/asset-borrowings/{id}/borrowed-units", [
        AssetBorrowingController::class,
        "getBorrowedUnits",
    ])->name("admin.asset-borrowings.borrowed-units");
    Route::post("/admin/asset-borrowings/{id}/receive", [
        AssetBorrowingController::class,
        "receive",
    ])->name("admin.asset-borrowings.receive");
    Route::post("/admin/asset-borrowings/{id}/confirm-replacement", [
        AssetBorrowingController::class,
        "confirmReplacement",
    ])->name("admin.asset-borrowings.confirm-replacement");
});

// Super Admin Only Routes
Route::middleware(["auth", "super_admin"])->group(function () {
    Route::get("/admin/users", [
        App\Http\Controllers\Admin\UserController::class,
        "index",
    ])->name("admin.users.index");
    Route::get("/admin/users/create", [
        App\Http\Controllers\Admin\UserController::class,
        "create",
    ])->name("admin.users.create");
    Route::post("/admin/users", [
        App\Http\Controllers\Admin\UserController::class,
        "store",
    ])->name("admin.users.store");
    Route::get("/admin/users/{user}/edit", [
        App\Http\Controllers\Admin\UserController::class,
        "edit",
    ])->name("admin.users.edit");
    Route::put("/admin/users/{user}", [
        App\Http\Controllers\Admin\UserController::class,
        "update",
    ])->name("admin.users.update");
    Route::put("/admin/users/{user}/reset-password", [
        App\Http\Controllers\Admin\UserController::class,
        "resetPassword",
    ])->name("admin.users.reset-password");
    Route::delete("/admin/users/{user}", [
        App\Http\Controllers\Admin\UserController::class,
        "destroy",
    ])->name("admin.users.destroy");
});
