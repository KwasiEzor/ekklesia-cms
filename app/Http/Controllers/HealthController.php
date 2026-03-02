<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
        ];

        $healthy = ! in_array(false, array_column($checks, 'ok'), true);

        return response()->json([
            'status' => $healthy ? 'healthy' : 'degraded',
            'checks' => $checks,
            'timestamp' => now()->toIso8601String(),
        ], $healthy ? 200 : 503);
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();

            return ['ok' => true];
        } catch (\Throwable) {
            return ['ok' => false, 'error' => 'Database connection failed'];
        }
    }

    private function checkCache(): array
    {
        try {
            $key = 'health_check_'.uniqid();
            Cache::put($key, true, 10);
            $result = Cache::get($key);
            Cache::forget($key);

            return ['ok' => $result === true];
        } catch (\Throwable) {
            return ['ok' => false, 'error' => 'Cache connection failed'];
        }
    }
}
