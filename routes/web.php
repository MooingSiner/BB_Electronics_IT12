<?php

use App\Enums\UserRole;
use App\Http\Controllers\Cashier\DashboardController as CashierDashboardController;
use App\Http\Controllers\Cashier\InventoryController as CashierInventoryController;
use App\Http\Controllers\Cashier\ProfileController as CashierProfileController;
use App\Http\Controllers\Cashier\ReturnController as CashierReturnController;
use App\Http\Controllers\Cashier\SalesController as CashierSalesController;
use App\Http\Controllers\Owner\AuditController as OwnerAuditController;
use App\Http\Controllers\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\Owner\InventoryController as OwnerInventoryController;
use App\Http\Controllers\Owner\ProfileController as OwnerProfileController;
use App\Http\Controllers\Owner\PurchaseOrderController as OwnerPurchaseOrderController;
use App\Http\Controllers\Owner\ReportController as OwnerReportController;
use App\Http\Controllers\Owner\ReturnController as OwnerReturnController;
use App\Http\Controllers\Owner\SalesController as OwnerSalesController;
use App\Http\Controllers\Owner\SupplierController as OwnerSupplierController;
use App\Http\Controllers\Owner\UserController as OwnerUserController;
use App\Http\Controllers\ProfileController;
use App\Livewire\Cashier\Pos;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect(auth()->check() ? route('dashboard') : route('login'));
});

Route::get('/dashboard', function () {
    return redirect(auth()->user()->role === UserRole::OwnerManager
        ? route('owner.dashboard')
        : route('cashier.dashboard'));
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:owner_manager'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', [OwnerDashboardController::class, 'index'])->name('dashboard');

    Route::get('/sales', [OwnerSalesController::class, 'index'])->name('sales.index');
    Route::get('/sales/{sale}', [OwnerSalesController::class, 'show'])->name('sales.show');
    Route::get('/sales/{sale}/receipt', [OwnerSalesController::class, 'receipt'])->name('sales.receipt');
    Route::post('/sales/{sale}/void', [OwnerSalesController::class, 'void'])->name('sales.void');

    Route::get('/inventory', [OwnerInventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/create', [OwnerInventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory', [OwnerInventoryController::class, 'store'])->name('inventory.store');
    Route::get('/inventory/stock-in', [OwnerInventoryController::class, 'bulkStockIn'])->name('inventory.stockin.bulk');
    Route::post('/inventory/stock-in', [OwnerInventoryController::class, 'bulkStockInStore'])->name('inventory.stockin.bulk.store');
    Route::get('/inventory/{product}', [OwnerInventoryController::class, 'show'])->name('inventory.show');
    Route::get('/inventory/{product}/label', [OwnerInventoryController::class, 'label'])->name('inventory.label');
    Route::get('/inventory/{product}/edit', [OwnerInventoryController::class, 'edit'])->name('inventory.edit');
    Route::put('/inventory/{product}', [OwnerInventoryController::class, 'update'])->name('inventory.update');
    Route::get('/inventory/{product}/stock-in', [OwnerInventoryController::class, 'stockIn'])->name('inventory.stockin');
    Route::post('/inventory/{product}/stock-in', [OwnerInventoryController::class, 'stockInStore'])->name('inventory.stockin.store');
    Route::get('/inventory/{product}/stock-out', [OwnerInventoryController::class, 'stockOut'])->name('inventory.stockout');
    Route::post('/inventory/{product}/stock-out', [OwnerInventoryController::class, 'stockOutStore'])->name('inventory.stockout.store');
    Route::get('/inventory/{product}/history', [OwnerInventoryController::class, 'history'])->name('inventory.history');
    Route::post('/inventory/{product}/history/{adjustment}/reverse', [OwnerInventoryController::class, 'reverseAdjustment'])->name('inventory.adjustment.reverse');
    Route::post('/inventory/{product}/archive', [OwnerInventoryController::class, 'archive'])->name('inventory.archive');
    Route::post('/inventory/{product}/restore', [OwnerInventoryController::class, 'restore'])->name('inventory.restore');

    Route::get('/suppliers', [OwnerSupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/orders', [OwnerSupplierController::class, 'orders'])->name('suppliers.orders');
    Route::get('/suppliers/create', [OwnerSupplierController::class, 'create'])->name('suppliers.create');
    Route::post('/suppliers', [OwnerSupplierController::class, 'store'])->name('suppliers.store');
    Route::get('/suppliers/damaged', [OwnerSupplierController::class, 'damagedIndex'])->name('suppliers.damaged');
    Route::get('/suppliers/damaged/{id}', [OwnerSupplierController::class, 'damagedShow'])->name('suppliers.damaged.show');
    Route::get('/suppliers/{order}', [OwnerSupplierController::class, 'show'])->name('suppliers.show');
    Route::get('/suppliers/{order}/receipt', [OwnerSupplierController::class, 'receipt'])->name('suppliers.receipt');
    Route::post('/suppliers/{order}/damage', [OwnerSupplierController::class, 'reportDamage'])->name('suppliers.damage');
    Route::delete('/suppliers/{order}/damage/{returnRecord}', [OwnerSupplierController::class, 'cancelDamage'])->name('suppliers.damage.cancel');
    Route::patch('/suppliers/{order}/return', [OwnerSupplierController::class, 'returnToSupplier'])->name('suppliers.return');
    Route::post('/suppliers/{order}/replacement', [OwnerSupplierController::class, 'replacement'])->name('suppliers.replacement');
    Route::post('/suppliers/{order}/receive', [OwnerSupplierController::class, 'receive'])->name('suppliers.receive');
    Route::post('/suppliers/{order}/cancel', [OwnerSupplierController::class, 'cancelOrder'])->name('suppliers.cancel');
    Route::post('/suppliers/{order}/archive', [OwnerSupplierController::class, 'archive'])->name('suppliers.archive');
    Route::post('/suppliers/{order}/restore', [OwnerSupplierController::class, 'restore'])->name('suppliers.restore');

    Route::get('/purchase-orders', [OwnerPurchaseOrderController::class, 'index'])->name('purchase-orders.index');
    Route::get('/purchase-orders/create', [OwnerPurchaseOrderController::class, 'create'])->name('purchase-orders.create');
    Route::post('/purchase-orders', [OwnerPurchaseOrderController::class, 'store'])->name('purchase-orders.store');
    Route::get('/purchase-orders/{order}', [OwnerPurchaseOrderController::class, 'show'])->name('purchase-orders.show');
    Route::get('/purchase-orders/{order}/receipt', [OwnerPurchaseOrderController::class, 'receipt'])->name('purchase-orders.receipt');
    Route::post('/purchase-orders/{order}/damage', [OwnerPurchaseOrderController::class, 'reportDamage'])->name('purchase-orders.damage');
    Route::delete('/purchase-orders/{order}/damage/{returnRecord}', [OwnerPurchaseOrderController::class, 'cancelDamage'])->name('purchase-orders.damage.cancel');
    Route::patch('/purchase-orders/{order}/return', [OwnerPurchaseOrderController::class, 'returnToStore'])->name('purchase-orders.return');
    Route::post('/purchase-orders/{order}/replacement', [OwnerPurchaseOrderController::class, 'replacement'])->name('purchase-orders.replacement');
    Route::post('/purchase-orders/{order}/receive', [OwnerPurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
    Route::post('/purchase-orders/{order}/cancel', [OwnerPurchaseOrderController::class, 'cancelOrder'])->name('purchase-orders.cancel');
    Route::post('/purchase-orders/{order}/archive', [OwnerPurchaseOrderController::class, 'archive'])->name('purchase-orders.archive');
    Route::post('/purchase-orders/{order}/restore', [OwnerPurchaseOrderController::class, 'restore'])->name('purchase-orders.restore');

    Route::get('/returns', [OwnerReturnController::class, 'index'])->name('returns.index');
    Route::post('/returns', [OwnerReturnController::class, 'store'])->name('returns.store');
    Route::get('/returns/create/{transaction}', [OwnerReturnController::class, 'create'])->name('returns.create');
    Route::get('/returns/process/{returnRecord?}', [OwnerReturnController::class, 'process'])->name('returns.process');
    Route::get('/returns/claim', [OwnerReturnController::class, 'claim'])->name('returns.claim');
    Route::post('/returns/claim', [OwnerReturnController::class, 'claimStore'])->name('returns.claim.store');
    Route::get('/returns/{returnRecord}', [OwnerReturnController::class, 'show'])->name('returns.show');
    Route::get('/returns/{returnRecord}/slip', [OwnerReturnController::class, 'slip'])->name('returns.slip');
    Route::patch('/returns/{returnRecord}/resolve', [OwnerReturnController::class, 'resolve'])->name('returns.resolve');
    Route::get('/returns/warranty/{warranty}', [OwnerReturnController::class, 'warranty'])->name('returns.warranty');
    Route::patch('/returns/warranty/{warranty}', [OwnerReturnController::class, 'warrantyUpdate'])->name('returns.warrantyUpdate');
    Route::patch('/returns/warranty/{warranty}/cancel', [OwnerReturnController::class, 'warrantyCancel'])->name('returns.warranty.cancel');
    Route::delete('/returns/{returnRecord}', [OwnerReturnController::class, 'cancel'])->name('returns.cancel');

    Route::get('/reports', [OwnerReportController::class, 'index'])->name('reports.index');

    Route::get('/users', [OwnerUserController::class, 'index'])->name('users.index');
    Route::post('/users', [OwnerUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [OwnerUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [OwnerUserController::class, 'update'])->name('users.update');
    Route::patch('/users/{user}/toggle-status', [OwnerUserController::class, 'toggleStatus'])->name('users.toggleStatus');

    Route::get('/audit-log', [OwnerAuditController::class, 'index'])->name('audit.index');

    Route::get('/profile', [OwnerProfileController::class, 'edit'])->name('profile');
    Route::patch('/profile', [OwnerProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [OwnerProfileController::class, 'password'])->name('profile.password');
});

Route::middleware(['auth', 'role:cashier_attendant'])->prefix('cashier')->name('cashier.')->group(function () {
    Route::get('/dashboard', [CashierDashboardController::class, 'index'])->name('dashboard');

    Route::get('/pos', Pos::class)->name('pos');

    Route::get('/sales', [CashierSalesController::class, 'index'])->name('sales.index');
    Route::get('/sales/{sale}', [CashierSalesController::class, 'show'])->name('sales.show');
    Route::get('/sales/{sale}/receipt', [CashierSalesController::class, 'receipt'])->name('sales.receipt');

    Route::get('/inventory', [CashierInventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/{product}', [CashierInventoryController::class, 'show'])->name('inventory.show');

    Route::get('/returns', [CashierReturnController::class, 'index'])->name('returns.index');
    Route::get('/returns/process', [CashierReturnController::class, 'process'])->name('returns.process');
    Route::post('/returns', [CashierReturnController::class, 'store'])->name('returns.store');
    Route::get('/returns/{returnRecord}/slip', [CashierReturnController::class, 'slip'])->name('returns.slip');
    Route::get('/returns/warranty/{warranty}', [CashierReturnController::class, 'warranty'])->name('returns.warranty');
    Route::patch('/returns/warranty/{warranty}/cancel', [CashierReturnController::class, 'warrantyCancel'])->name('returns.warranty.cancel');
    Route::delete('/returns/{returnRecord}', [CashierReturnController::class, 'cancel'])->name('returns.cancel');
    Route::get('/returns/claim', [CashierReturnController::class, 'claim'])->name('returns.claim');
    Route::post('/returns/claim', [CashierReturnController::class, 'claimStore'])->name('returns.claim.store');
    Route::get('/returns/{returnRecord}', [CashierReturnController::class, 'show'])->name('returns.show');

    Route::get('/profile', [CashierProfileController::class, 'edit'])->name('profile');
    Route::patch('/profile', [CashierProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [CashierProfileController::class, 'password'])->name('profile.password');
});

require __DIR__.'/auth.php';
