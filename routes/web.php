<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\RiskController;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PortController;
use App\Http\Controllers\VisualizationController;
use App\Http\Controllers\ComparisonController;
use App\Http\Controllers\WatchlistController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\PortController as AdminPortController;
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\WordController as AdminWordController;
use App\Http\Controllers\Admin\ApiLogController as AdminApiLogController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {
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

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('admin')
        ->group(function () {
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

            Route::get(
                '/ports',
                [AdminPortController::class, 'index']
            )->name('ports.index');

            Route::post(
                '/ports',
                [AdminPortController::class, 'store']
            )->name('ports.store');

            Route::put(
                '/ports/{port}',
                [AdminPortController::class, 'update']
            )->name('ports.update');

            Route::delete(
                '/ports/{port}',
                [AdminPortController::class, 'destroy']
            )->name('ports.destroy');

            Route::get(
                '/articles',
                [AdminArticleController::class, 'index']
            )->name('articles.index');

            Route::get(
                '/words',
                [AdminWordController::class, 'index']
            )->name('words.index');

            Route::get(
                '/api-logs',
                [AdminApiLogController::class, 'index']
            )->name('api-logs.index');
        });
});

require __DIR__.'/auth.php';