<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

// Authentication routes
Route::post('/register', [AuthController::class, 'register'])
    ->middleware(\App\Http\Middleware\ResolveTenantServices::class);
Route::post('/login', [AuthController::class, 'login'])
    ->middleware(\App\Http\Middleware\ResolveTenantServices::class);

// Protected authentication routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::get('/tokens', [AuthController::class, 'tokens']);
});

Route::get('/status', [HealthController::class, 'status'])
    ->middleware(\App\Http\Middleware\ResolveTenantServices::class);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Protected routes - Projects and Orders
Route::middleware('auth:sanctum')->group(function () {
    // Project routes
    Route::get('/projects', [ProjectController::class, 'index']);

    // Order routes - Apply tenant service resolution for customization
    Route::middleware(\App\Http\Middleware\ResolveTenantServices::class)->group(function () {
        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{guid}', [OrderController::class, 'show']);
        Route::put('/orders/{guid}', [OrderController::class, 'update']);
        Route::delete('/orders/{guid}', [OrderController::class, 'destroy']);
    });
});

// Cache test endpoints
Route::post('/cache/write', function (Request $request) {
    $key = $request->input('key', 'test_key');
    $value = $request->input('value', 'test_value');
    $ttl = $request->input('ttl', 3600); // default 1 hour

    try {
        Cache::put($key, $value, $ttl);

        return response()->json([
            'success' => true,
            'message' => 'Cache write successful',
            'data' => [
                'key' => $key,
                'value' => $value,
                'ttl' => $ttl,
            ],
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Cache write failed',
            'error' => $e->getMessage(),
        ], 500);
    }
});

Route::get('/cache/read/{key}', function ($key) {
    try {
        $value = Cache::get($key);

        if ($value === null) {
            return response()->json([
                'success' => false,
                'message' => 'Key not found in cache',
                'data' => [
                    'key' => $key,
                    'value' => null,
                ],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cache read successful',
            'data' => [
                'key' => $key,
                'value' => $value,
            ],
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Cache read failed',
            'error' => $e->getMessage(),
        ], 500);
    }
});
