<?php

use App\Enums\UserRole;
use App\Http\Controllers\Cashier\DashboardController as CashierDashboardController;
use App\Http\Controllers\Cashier\InventoryController as CashierInventoryController;
use App\Http\Controllers\Cashier\PosController;
use App\Http\Controllers\Cashier\ProfileController as CashierProfileController;
use App\Http\Controllers\Cashier\ReturnController as CashierReturnController;
use App\Http\Controllers\Cashier\SalesController as CashierSalesController;
use App\Http\Controllers\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\Owner\InventoryController as OwnerInventoryController;
use App\Http\Controllers\Owner\ProfileController as OwnerProfileController;
use App\Http\Controllers\Owner\ReportController as OwnerReportController;
use App\Http\Controllers\Owner\ReturnController as OwnerReturnController;
use App\Http\Controllers\Owner\SalesController as OwnerSalesController;
use App\Http\Controllers\Owner\SupplierController as OwnerSupplierController;
use App\Http\Controllers\Owner\UserController as OwnerUserController;
use App\Http\Controllers\ProfileController;
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
    Route::get('/sales/create', [OwnerSalesController::class, 'create'])->name('sales.create');
    Route::post('/sales', [OwnerSalesController::class, 'store'])->name('sales.store');
    Route::get('/sales/{sale}', [OwnerSalesController::class, 'show'])->name('sales.show');
    Route::get('/sales/{sale}/receipt', [OwnerSalesController::class, 'receipt'])->name('sales.receipt');

    Route::get('/inventory', [OwnerInventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/create', [OwnerInventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory', [OwnerInventoryController::class, 'store'])->name('inventory.store');
    Route::get('/inventory/{product}', [OwnerInventoryController::class, 'show'])->name('inventory.show');
    Route::get('/inventory/{product}/edit', [OwnerInventoryController::class, 'edit'])->name('inventory.edit');
    Route::post('/inventory/{product}/stock-in', [OwnerInventoryController::class, 'stockIn'])->name('inventory.stockin');
    Route::get('/inventory/{product}/history', [OwnerInventoryController::class, 'history'])->name('inventory.history');

    Route::get('/suppliers', [OwnerSupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/orders', [OwnerSupplierController::class, 'orders'])->name('suppliers.orders');
    Route::get('/suppliers/create', [OwnerSupplierController::class, 'create'])->name('suppliers.create');
    Route::post('/suppliers', [OwnerSupplierController::class, 'store'])->name('suppliers.store');
    Route::get('/suppliers/damaged', [OwnerSupplierController::class, 'damagedIndex'])->name('suppliers.damaged');
    Route::get('/suppliers/damaged/{id}', [OwnerSupplierController::class, 'damagedShow'])->name('suppliers.damaged.show');
    Route::get('/suppliers/{supplier}', [OwnerSupplierController::class, 'show'])->name('suppliers.show');
    Route::post('/suppliers/{supplier}/damage', [OwnerSupplierController::class, 'reportDamage'])->name('suppliers.damage');
    Route::post('/suppliers/{supplier}/return', [OwnerSupplierController::class, 'returnToSupplier'])->name('suppliers.return');
    Route::post('/suppliers/{supplier}/replacement', [OwnerSupplierController::class, 'replacement'])->name('suppliers.replacement');
    Route::post('/suppliers/{supplier}/receive', [OwnerSupplierController::class, 'receive'])->name('suppliers.receive');

    Route::get('/returns', [OwnerReturnController::class, 'index'])->name('returns.index');
    Route::post('/returns', [OwnerReturnController::class, 'store'])->name('returns.store');
    Route::get('/returns/create/{transaction}', [OwnerReturnController::class, 'create'])->name('returns.create');
    Route::get('/returns/process/{returnRecord?}', [OwnerReturnController::class, 'process'])->name('returns.process');
    Route::get('/returns/{returnRecord}', [OwnerReturnController::class, 'show'])->name('returns.show');
    Route::get('/returns/warranty/{warranty}', [OwnerReturnController::class, 'warranty'])->name('returns.warranty');
    Route::post('/returns/warranty/{warranty}', [OwnerReturnController::class, 'warrantyUpdate'])->name('returns.warrantyUpdate');

    Route::get('/reports', [OwnerReportController::class, 'index'])->name('reports.index');

    Route::get('/users', [OwnerUserController::class, 'index'])->name('users.index');
    Route::post('/users', [OwnerUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [OwnerUserController::class, 'edit'])->name('users.edit');
    Route::post('/users/{user}/toggle-status', [OwnerUserController::class, 'toggleStatus'])->name('users.toggleStatus');

    Route::get('/profile', [OwnerProfileController::class, 'edit'])->name('profile');
    Route::patch('/profile', [OwnerProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [OwnerProfileController::class, 'password'])->name('profile.password');
});

Route::middleware(['auth', 'role:cashier_attendant'])->prefix('cashier')->name('cashier.')->group(function () {
    Route::get('/dashboard', [CashierDashboardController::class, 'index'])->name('dashboard');

    Route::get('/pos', [PosController::class, 'index'])->name('pos');
    Route::post('/pos/add', [PosController::class, 'add'])->name('pos.add');
    Route::post('/pos/remove', [PosController::class, 'remove'])->name('pos.remove');
    Route::post('/pos/update-qty', [PosController::class, 'updateQuantity'])->name('pos.update-qty');
    Route::post('/pos/clear', [PosController::class, 'clear'])->name('pos.clear');
    Route::post('/pos/discount', [PosController::class, 'discount'])->name('pos.discount');
    Route::post('/pos/payment', [PosController::class, 'payment'])->name('pos.payment');
    Route::post('/pos/amount', [PosController::class, 'amount'])->name('pos.amount');
    Route::post('/pos/complete', [PosController::class, 'complete'])->name('pos.complete');

    Route::get('/sales', [CashierSalesController::class, 'index'])->name('sales.index');
    Route::get('/sales/{sale}', [CashierSalesController::class, 'show'])->name('sales.show');
    Route::get('/sales/{sale}/receipt', [CashierSalesController::class, 'receipt'])->name('sales.receipt');

    Route::get('/inventory', [CashierInventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/{product}', [CashierInventoryController::class, 'show'])->name('inventory.show');

    Route::get('/returns', [CashierReturnController::class, 'index'])->name('returns.index');
    Route::get('/returns/process', [CashierReturnController::class, 'process'])->name('returns.process');
    Route::post('/returns', [CashierReturnController::class, 'store'])->name('returns.store');
    Route::get('/returns/warranty/{warranty}', [CashierReturnController::class, 'warranty'])->name('returns.warranty');
    Route::get('/returns/{returnRecord}', [CashierReturnController::class, 'show'])->name('returns.show');

    Route::get('/profile', [CashierProfileController::class, 'edit'])->name('profile');
    Route::patch('/profile', [CashierProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [CashierProfileController::class, 'password'])->name('profile.password');
});

require __DIR__.'/auth.php';
