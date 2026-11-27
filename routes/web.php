<?php

use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SparepartController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AdminController;

/*
| Web Routes
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// GRUP ROUTE YANG WAJIB LOGIN
Route::middleware('auth')->group(function () {
    // Profil User
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- MASTER SPAREPART ---
    // 1. Halaman List
    Route::get('/spareparts', [SparepartController::class, 'index'])->name('spareparts.index');

    // 2. Halaman Create & Proses Simpan
    Route::get('/spareparts/create', [SparepartController::class, 'create'])->name('spareparts.create');
    Route::post('/spareparts', [SparepartController::class, 'store'])->name('spareparts.store');

    // 3. Detail Sparepart
    Route::get('/spareparts/{id}', [SparepartController::class, 'show'])->name('spareparts.show');

    // 4. Halaman Edit & Proses Update
    Route::get('/spareparts/{id}/edit', [SparepartController::class, 'edit'])->name('spareparts.edit');
    Route::put('/spareparts/{id}', [SparepartController::class, 'update'])->name('spareparts.update');

    // 5. Proses Hapus
    Route::delete('/spareparts/{id}', [SparepartController::class, 'destroy'])->name('spareparts.destroy');

    // 6. --- TRANSACTIONS (GOODS RECEIPT) ---
    Route::get('/transactions/good-receipt', [TransactionController::class, 'createGR'])->name('transactions.gr.create');
    Route::post('/transactions/good-receipt', [TransactionController::class, 'storeGR'])->name('transactions.gr.store');

    // 7. --- GOODS ISSUE (Barang Keluar) ---
    Route::get('/transactions/good-issue', [TransactionController::class, 'createGI'])->name('transactions.gi.create');
    Route::post('/transactions/good-issue', [TransactionController::class, 'storeGI'])->name('transactions.gi.store');
    Route::get('/transactions/gi/{id}/pdf', [TransactionController::class, 'downloadGI'])->name('transactions.gi.pdf');
    Route::post('/transactions/gi/bulk-pdf', [TransactionController::class, 'downloadBulkGI'])->name('transactions.gi.bulkPdf');

    // 8. --- TRANSACTION HISTORY (LIST) ---
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/proofs/{detail}/{type}', [TransactionController::class, 'showInstallationProof'])->whereIn('type', ['before', 'after'])->name('transactions.proof');

    // 9. --- PENDING ISSUES (GI - Barang Keluar) ---
    Route::get('/transactions/pending', [TransactionController::class, 'indexPending'])->name('transactions.pending');
    Route::put('/transactions/pending/{id}', [TransactionController::class, 'updatePending'])->name('transactions.pending.update');

    // 10. --- PENDING GR (Barang Masuk via Vendor) --- [BARU]
    // Halaman List Pending GR
    Route::get('/transactions/pending-gr', [TransactionController::class, 'indexPendingGR'])->name('transactions.pendingGR');
    
    // Proses Submit PO & BAST untuk banyak item sekaligus
    Route::post('/transactions/pending-gr/process', [TransactionController::class, 'processPendingGR'])->name('transactions.pendingGR.process');
    
    // Route untuk Cancel Pending GR (Revert Stock)
    Route::delete('/transactions/pending-gr/{id}', [TransactionController::class, 'destroyPendingGR'])->name('transactions.pendingGR.destroy');
    
    // BARU: Route Warehouse Layout
    Route::get('/warehouse/layout', [\App\Http\Controllers\WarehouseController::class, 'layout'])->name('warehouse.layout');
    Route::post('/warehouse/layout', [\App\Http\Controllers\WarehouseController::class, 'updateLayout'])->name('warehouse.layout.update');

    Route::get('/api/check-po', [TransactionController::class, 'checkPO']);

    // API Khusus untuk Live Search Sparepart
    Route::get('/api/spareparts/search', [TransactionController::class, 'searchParts'])->name('api.spareparts.search');

    // Controller khusus notifikasi
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');

    // Purchase Order List and Details
    Route::get('/transactions/purchase-orders', [TransactionController::class, 'indexPO'])->name('transactions.po.index');
    Route::get('/transactions/purchase-orders/{po_number}', [TransactionController::class, 'showPO'])->name('transactions.po.show');

    //clear notification
    Route::delete('/notifications/clear-all', [NotificationController::class, 'clearAll'])->name('notifications.clear');

    // --- REPORTING (DOWNLOAD DATA) ---
    Route::get('/reports/export-stock', [ReportController::class, 'exportStock'])->name('reports.exportStock');

    // --- REPORTING ---
    // (Existing)
    Route::get('/reports/export-stock', [ReportController::class, 'exportStock'])->name('reports.exportStock');
    
    // (BARU - Sisipkan disini)
    Route::get('/reports/export-gi', [ReportController::class, 'exportGI'])->name('reports.exportGI');
    Route::get('/reports/export-gr', [ReportController::class, 'exportGR'])->name('reports.exportGR');
    
    // (Existing)
    Route::get('/backup/download', [ReportController::class, 'downloadBackup'])->name('backup.download');

    // --- ADMIN TOOLS ---
    // Halaman Utama Admin (Tabbed Interface)
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    
    // Tab 1: User Manager
    Route::post('/admin/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
    Route::delete('/admin/users/{id}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');
    Route::put('/admin/users/{id}/password', [AdminController::class, 'updateUserPassword'])->name('admin.users.password');
    
    // Tab 2: Transaction Removal
    Route::delete('/admin/transactions/{id}', [AdminController::class, 'destroyTransaction'])->name('admin.transactions.destroy');
    
    // Tab 3: Backup & Restore
    // Note: Route backup.download sudah ada di ReportController sebelumnya, jadi di sini cukup restore
    Route::post('/admin/restore', [AdminController::class, 'restoreBackup'])->name('admin.restore');
    Route::post('/admin/restore/chunk', [AdminController::class, 'restoreChunk'])->name('admin.restore.chunk');
    Route::post('/admin/restore/finalize', [AdminController::class, 'restoreFinalize'])->name('admin.restore.finalize');

    // Tab 4: Manage Vehicles
    Route::post('/admin/vehicles', [AdminController::class, 'storeVehicle'])->name('admin.vehicles.store');
    Route::delete('/admin/vehicles/{id}', [AdminController::class, 'destroyVehicle'])->name('admin.vehicles.destroy');

    

});

require __DIR__ . '/auth.php';
