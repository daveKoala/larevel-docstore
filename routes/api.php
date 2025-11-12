<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

Route::get('/status', function () {
    $status = [
        'app' => [
            'name' => config('app.name'),
            'env' => config('app.env'),
            'debug' => config('app.debug'),
            'status' => 'running',
        ],
        'server' => [
            'time' => now()->toDateTimeString(),
            'timezone' => config('app.timezone'),
            'timestamp' => now()->timestamp,
        ],
        'cache' => [
            'driver' => config('cache.default'),
            'status' => 'unknown',
        ],
        'database' => [
            'connection' => config('database.default'),
            'status' => 'unknown',
        ],
    ];

    // Test cache connection
    try {
        $testKey = 'status_check_' . time();
        Cache::put($testKey, 'test', 5);
        $retrieved = Cache::get($testKey);
        Cache::forget($testKey);

        $status['cache']['status'] = ($retrieved === 'test') ? 'connected' : 'error';
    } catch (\Exception $e) {
        $status['cache']['status'] = 'error';
        $status['cache']['message'] = $e->getMessage();
    }

    // Test database connection
    try {
        DB::connection()->getPdo();
        $status['database']['status'] = 'connected';
        $status['database']['name'] = DB::connection()->getDatabaseName();
    } catch (\Exception $e) {
        $status['database']['status'] = 'error';
        $status['database']['message'] = $e->getMessage();
    }

    return response()->json($status);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

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
