<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\RiskController;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PortController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VisualizationController;
use App\Http\Controllers\ComparisonController;
use App\Http\Controllers\WatchlistController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\PortController as AdminPortController;
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\WordController as AdminWordController;
use App\Http\Controllers\Admin\ApiLogController as AdminApiLogController;
use App\Http\Controllers\Admin\CountryController as AdminCountryController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Halaman Utama Pengguna
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/dashboard',
        [DashboardController::class, 'index']
    )->name('dashboard');

    Route::get(
        '/countries',
        [CountryController::class, 'index']
    )->name('countries.index');

    Route::get(
        '/risk-scoring',
        [RiskController::class, 'index']
    )->name('risk.index');

    Route::get(
        '/weather-monitoring',
        [WeatherController::class, 'index']
    )->name('weather.index');

    Route::get(
        '/currency-impact',
        [CurrencyController::class, 'index']
    )->name('currency.index');

    Route::get(
        '/news-intelligence',
        [NewsController::class, 'index']
    )->name('news.index');

    Route::get(
        '/port-location',
        [PortController::class, 'index']
    )->name('ports.index');

    Route::get(
        '/data-visualization',
        [VisualizationController::class, 'index']
    )->name('visualization.index');

    Route::get(
        '/country-comparison',
        [ComparisonController::class, 'index']
    )->name('comparison.index');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | Daftar Pemantauan Favorit
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/favorite-monitoring',
        [WatchlistController::class, 'index']
    )->name('watchlist.index');

    Route::post(
        '/favorite-monitoring',
        [WatchlistController::class, 'store']
    )->name('watchlist.store');

    Route::delete(
        '/favorite-monitoring/{countryCode}',
        [WatchlistController::class, 'destroyByCountry']
    )->name('watchlist.destroy');

    /*
    |--------------------------------------------------------------------------
    | Halaman Khusus Administrator
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('admin')
        ->group(function () {

            Route::get(
                '/countries',
                [AdminCountryController::class, 'index']
            )->name('countries.index');

            Route::post(
                '/countries',
                [AdminCountryController::class, 'store']
            )->name('countries.store');

            Route::post(
                '/countries/sync',
                [AdminCountryController::class, 'sync']
            )->name('countries.sync');

            Route::put(
                '/countries/{country}',
                [AdminCountryController::class, 'update']
            )->name('countries.update');

            Route::delete(
                '/countries/{country}',
                [AdminCountryController::class, 'destroy']
            )->name('countries.destroy');

            /*
            |--------------------------------------------------------------------------
            | Manajemen Pengguna
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/users',
                [AdminUserController::class, 'index']
            )->name('users.index');

            Route::patch(
                '/users/{user}/role',
                [AdminUserController::class, 'updateRole']
            )->name('users.update-role');

            Route::delete(
                '/users/{user}',
                [AdminUserController::class, 'destroy']
            )->name('users.destroy');

            /*
            |--------------------------------------------------------------------------
            | Kelola Pelabuhan
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/ports',
                [AdminPortController::class, 'index']
            )->name('ports.index');

            Route::post(
                '/ports',
                [AdminPortController::class, 'store']
            )->name('ports.store');

            Route::post(
                '/ports/sync-wpi',
                [AdminPortController::class, 'syncWorldPortIndex']
            )->name('ports.sync-wpi');

            Route::put(
                '/ports/{port}',
                [AdminPortController::class, 'update']
            )->name('ports.update');

            Route::delete(
                '/ports/{port}',
                [AdminPortController::class, 'destroy']
            )->name('ports.destroy');

            /*
            |--------------------------------------------------------------------------
            | Manajemen Artikel
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/articles',
                [AdminArticleController::class, 'index']
            )->name('articles.index');

            Route::post(
                '/articles',
                [AdminArticleController::class, 'store']
            )->name('articles.store');

            Route::put(
                '/articles/{article}',
                [AdminArticleController::class, 'update']
            )->name('articles.update');

            Route::delete(
                '/articles/{article}',
                [AdminArticleController::class, 'destroy']
            )->name('articles.destroy');

            /*
            |--------------------------------------------------------------------------
            | Kamus Kata Sentimen
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/words',
                [AdminWordController::class, 'index']
            )->name('words.index');

            Route::post(
                '/words',
                [AdminWordController::class, 'store']
            )->name('words.store');

            Route::put(
                '/words/{word}',
                [AdminWordController::class, 'update']
            )->name('words.update');

            Route::delete(
                '/words/{word}',
                [AdminWordController::class, 'destroy']
            )->name('words.destroy');

            /*
            |--------------------------------------------------------------------------
            | Log API
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/api-logs',
                [AdminApiLogController::class, 'index']
            )->name('api-logs.index');
        });
});

require __DIR__.'/auth.php';
