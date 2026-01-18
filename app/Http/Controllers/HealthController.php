<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Health check endpoints for monitoring
 */
class HealthController extends Controller
{
    /**
     * Basic health check
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'service' => config('app.name'),
            'version' => config('app.version', '1.0.0'),
        ]);
    }

    /**
     * Detailed health check
     */
    public function detailed(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
        ];

        $allHealthy = ! in_array(false, array_column($checks, 'healthy'));

        return response()->json([
            'status' => $allHealthy ? 'healthy' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
        ], $allHealthy ? 200 : 503);
    }

    /**
     * Check database connectivity
     */
    protected function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();

            return [
                'healthy' => true,
                'message' => 'Database connection successful',
            ];
        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'message' => 'Database connection failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check cache functionality
     */
    protected function checkCache(): array
    {
        try {
            $key = 'health_check_'.time();
            $value = 'test';

            Cache::put($key, $value, 60);
            $retrieved = Cache::get($key);
            Cache::forget($key);

            return [
                'healthy' => $retrieved === $value,
                'message' => $retrieved === $value ? 'Cache working' : 'Cache test failed',
                'driver' => config('cache.default'),
            ];
        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'message' => 'Cache check failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check storage accessibility
     */
    protected function checkStorage(): array
    {
        try {
            $testFile = 'health_check_'.time().'.txt';
            Storage::disk('public')->put($testFile, 'test');
            $exists = Storage::disk('public')->exists($testFile);
            Storage::disk('public')->delete($testFile);

            return [
                'healthy' => $exists,
                'message' => $exists ? 'Storage accessible' : 'Storage test failed',
            ];
        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'message' => 'Storage check failed',
                'error' => $e->getMessage(),
            ];
        }
    }
}
