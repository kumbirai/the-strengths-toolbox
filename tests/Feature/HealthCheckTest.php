<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    /**
     * Test basic health check endpoint
     */
    public function test_health_check_returns_ok(): void
    {
        $response = $this->getJson('/health');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'timestamp',
            'service',
            'version',
        ]);
        $response->assertJson([
            'status' => 'ok',
        ]);
    }

    /**
     * Test detailed health check endpoint
     */
    public function test_detailed_health_check_returns_status(): void
    {
        $response = $this->getJson('/health/detailed');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'timestamp',
            'checks' => [
                'database' => ['healthy', 'message'],
                'cache' => ['healthy', 'message'],
                'storage' => ['healthy', 'message'],
            ],
        ]);
    }

    /**
     * Test detailed health check verifies database
     */
    public function test_detailed_health_check_verifies_database(): void
    {
        $response = $this->getJson('/health/detailed');

        $response->assertStatus(200);
        $this->assertTrue($response->json('checks.database.healthy'));
    }

    /**
     * Test detailed health check verifies cache
     */
    public function test_detailed_health_check_verifies_cache(): void
    {
        $response = $this->getJson('/health/detailed');

        $response->assertStatus(200);
        $this->assertTrue($response->json('checks.cache.healthy'));
    }

    /**
     * Test detailed health check verifies storage
     */
    public function test_detailed_health_check_verifies_storage(): void
    {
        $response = $this->getJson('/health/detailed');

        $response->assertStatus(200);
        $this->assertTrue($response->json('checks.storage.healthy'));
    }
}
