<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DropdownOptionController;
use App\Http\Controllers\Api\RecordController;
use App\Models\Permission;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes (no authentication required)
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
});

// Protected routes (authentication required)
Route::middleware('auth:api')->group(function () {

    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/user', [AuthController::class, 'user'])->name('auth.user');
    });

    // Dropdown options (read access for all authenticated users)
    Route::middleware('permission:' . Permission::DROPDOWN_OPTIONS_VIEW)->group(function () {
        Route::get('/dropdown-options', [DropdownOptionController::class, 'index'])
            ->name('dropdown-options.index');
        Route::get('/dropdown-options/{type}', [DropdownOptionController::class, 'byType'])
            ->name('dropdown-options.by-type');
    });

    // Dropdown options management (requires manage permission)
    Route::middleware('permission:' . Permission::DROPDOWN_OPTIONS_MANAGE)->group(function () {
        Route::post('/dropdown-options', [DropdownOptionController::class, 'store'])
            ->name('dropdown-options.store');
        Route::put('/dropdown-options/{dropdownOption}', [DropdownOptionController::class, 'update'])
            ->name('dropdown-options.update');
        Route::delete('/dropdown-options/{dropdownOption}', [DropdownOptionController::class, 'destroy'])
            ->name('dropdown-options.destroy');
    });

    // Records - Authorization handled by RecordPolicy via controller
    Route::apiResource('records', RecordController::class)->names([
        'index' => 'records.index',
        'store' => 'records.store',
        'show' => 'records.show',
        'update' => 'records.update',
        'destroy' => 'records.destroy',
    ]);
});
