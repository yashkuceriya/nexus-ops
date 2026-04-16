<?php

use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\SensorController;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Api\WorkOrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api and return JSON responses.
| Authenticated routes use Sanctum token-based auth and scope
| all queries to the user's tenant_id.
|
*/

// All API routes are rate-limited and versioned
Route::middleware('throttle:api')->prefix('v1')->group(function (): void {

// Public auth routes
Route::post('/auth/login', [AuthController::class, 'login'])
    ->name('api.auth.login');

// Authenticated routes (Sanctum)
Route::middleware('auth:sanctum')->group(function (): void {

    // Auth
    Route::get('/auth/me', [AuthController::class, 'me'])->name('api.auth.me');
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.auth.logout');

    // Dashboard
    Route::prefix('dashboard')->name('api.dashboard.')->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/projects/{projectId}/readiness', [DashboardController::class, 'projectReadiness'])
            ->where('projectId', '[0-9]+')
            ->name('project-readiness');
        Route::get('/kpis', [DashboardController::class, 'kpis'])->name('kpis');
        Route::get('/sensors', [DashboardController::class, 'sensorOverview'])->name('sensor-overview');
    });

    // Work Orders
    Route::prefix('work-orders')->name('api.work-orders.')->group(function (): void {
        Route::get('/', [WorkOrderController::class, 'index'])->name('index');
        Route::post('/', [WorkOrderController::class, 'store'])->name('store');
        Route::get('/{id}', [WorkOrderController::class, 'show'])
            ->where('id', '[0-9]+')
            ->name('show');
        Route::put('/{id}', [WorkOrderController::class, 'update'])
            ->where('id', '[0-9]+')
            ->name('update');
        Route::patch('/{id}/status', [WorkOrderController::class, 'updateStatus'])
            ->where('id', '[0-9]+')
            ->name('update-status');
    });

    // Assets
    Route::prefix('assets')->name('api.assets.')->group(function (): void {
        Route::get('/', [AssetController::class, 'index'])->name('index');
        Route::get('/qr/{code}', [AssetController::class, 'qrLookup'])->name('qr-lookup');
        Route::get('/{id}', [AssetController::class, 'show'])
            ->where('id', '[0-9]+')
            ->name('show');
    });

    // Sensors
    Route::prefix('sensors')->name('api.sensors.')->group(function (): void {
        Route::get('/', [SensorController::class, 'index'])->name('index');
        Route::post('/ingest', [SensorController::class, 'ingest'])->name('ingest');
        Route::get('/{sensorSourceId}/readings', [SensorController::class, 'readings'])
            ->where('sensorSourceId', '[0-9]+')
            ->name('readings');
    });

    // Sync
    Route::prefix('sync')->name('api.sync.')->group(function (): void {
        Route::post('/trigger', [SyncController::class, 'triggerSync'])->name('trigger');
        Route::get('/status', [SyncController::class, 'status'])->name('status');
    });
});

}); // end throttle:api + v1 prefix
