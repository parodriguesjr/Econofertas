<?php

use App\Http\Controllers\Admin\FlyerController;
use App\Http\Controllers\Admin\SupermarketController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OfferController;
use App\Http\Middleware\BlockTunnelAdmin;
use App\Models\Flyer;
use App\Models\Supermarket;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/ofertas', [OfferController::class, 'index'])->name('offers.index');
Route::get('/supermercados/{supermarket:slug}', [HomeController::class, 'supermarket'])->name('supermarkets.show');
Route::get('/folhetos/{flyer}/visualizar', [HomeController::class, 'flyer'])->name('flyers.view');

Route::middleware([BlockTunnelAdmin::class, 'guest'])->group(function () {
    Route::get('/admin/entrar', [AuthController::class, 'create'])->name('login');
    Route::post('/admin/entrar', [AuthController::class, 'store'])->name('login.store');
});

Route::prefix('admin')->name('admin.')->middleware([BlockTunnelAdmin::class, 'auth'])->group(function () {
    Route::get('/', fn () => view('admin.dashboard', [
        'supermarketsCount' => Supermarket::count(),
        'flyersCount' => Flyer::count(),
        'activeFlyersCount' => Flyer::available()->count(),
    ]))->name('dashboard');
    Route::resource('supermercados', SupermarketController::class)->parameters(['supermercados' => 'supermarket'])->except('show')->names('supermarkets');
    Route::get('/folhetos/{flyer}/pre-visualizar', [FlyerController::class, 'preview'])->name('flyers.preview');
    Route::resource('folhetos', FlyerController::class)->parameters(['folhetos' => 'flyer'])->except('show')->names('flyers');
    Route::post('/sair', [AuthController::class, 'destroy'])->name('logout');
});
