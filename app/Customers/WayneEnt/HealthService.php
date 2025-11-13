<?php

namespace App\Customers\WayneEnt;

use App\Contracts\HealthServiceInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthService implements HealthServiceInterface
{
    public function getStatus(): array
    {
        $status = [
            'tenant' => 'WayneEnt',
            'app' => [
                'name' => config('app.name'),
                'env' => config('app.env'),
                'debug' => config('app.debug'),
                'status' => 'running',
                'custom_service' => 'WayneEnt Custom Health Service',
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
            $testKey = 'wayne_status_check_' . time();
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

        // Add WayneEnt-specific checks
        $status['wayne_custom'] = [
            'special_service' => 'operational',
            'bat_signal' => 'ready',
        ];

        return $status;
    }
}
