<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PartController;
use App\Http\Controllers\QuoteRequestController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/catalog/{category:slug}', [CatalogController::class, 'show'])->name('catalog.category');
Route::get('/catalog/{category:slug}/{part:slug}', [PartController::class, 'show'])
    ->scopeBindings()
    ->name('catalog.part');

Route::get('/search', SearchController::class)->name('search');

Route::post('/cart/{part:slug}', [CartController::class, 'store'])
    ->name('cart.store');
Route::patch('/cart/{part:slug}', [CartController::class, 'update'])
    ->name('cart.update');
Route::delete('/cart/{part:slug}', [CartController::class, 'destroy'])
    ->name('cart.destroy');

Route::get('/quote', [QuoteRequestController::class, 'index'])->name('quote.index');
Route::post('/quote', [QuoteRequestController::class, 'store'])
    ->middleware('throttle:quote')
    ->name('quote.store');

Route::post('/callback', [LeadController::class, 'store'])
    ->middleware('throttle:lead')
    ->name('lead.store');

// Статические страницы из админки — последним правилом, чтобы не перекрывать другие маршруты.
Route::get('/{page:slug}', PageController::class)->name('pages.show');
