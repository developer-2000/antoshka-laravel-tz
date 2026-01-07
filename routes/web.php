<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect()->route('orders.index');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('orders', OrderController::class);

Route::get('/inventory', [InventoryController::class, 'index'])
    ->name('inventory.index');
Route::get('/inventory/{sku}/movements', [InventoryController::class, 'movements'])
    ->name('inventory.movements');
