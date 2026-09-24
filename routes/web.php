<?php

use App\Enums\UserRole;
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
    return view('welcome');
});

Route::get('/dashboard', function () {
    return redirect(auth()->user()->role === UserRole::OwnerManager
        ? route('owner.dashboard')
        : route('dashboard.fallback'));
})->middleware(['auth'])->name('dashboard');

// Placeholder landing page for roles without a dedicated dashboard yet (e.g. cashier).
Route::get('/dashboard/pending', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard.fallback');

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

require __DIR__.'/auth.php';
