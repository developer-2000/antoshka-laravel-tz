<?php

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;


Route::post('/order', [OrderController::class, 'store']);
Route::get('/orders/{id}', [OrderController::class, 'show']);

Route::get('/inventory/{sku}/movements', [InventoryController::class, 'movements']);

Route::post('/supplier/reserve', [SupplierController::class, 'reserve']);
Route::get('/supplier/status/{ref}', [SupplierController::class, 'status']);
