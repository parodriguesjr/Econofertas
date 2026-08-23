<?php

use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\SupermarketController;
use Illuminate\Support\Facades\Route;

Route::get('/supermercados', [SupermarketController::class, 'index']);
Route::get('/supermercados/{supermarket:slug}', [SupermarketController::class, 'show']);
Route::get('/ofertas', [OfferController::class, 'index']);
