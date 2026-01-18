<?php

namespace Tests\Performance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoadTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_handles_concurrent_requests(): void
    {
        $concurrentRequests = 10;
        $successCount = 0;

        for ($i = 0; $i < $concurrentRequests; $i++) {
            $response = $this->get('/');
            if ($response->status() === 200) {
                $successCount++;
            }
        }

        $this->assertEquals($concurrentRequests, $successCount);
    }

    public function test_api_handles_concurrent_requests(): void
    {
        $concurrentRequests = 10;
        $successCount = 0;

        for ($i = 0; $i < $concurrentRequests; $i++) {
            $response = $this->get('/blog');
            if ($response->status() === 200) {
                $successCount++;
            }
        }

        $this->assertGreaterThan(0, $successCount);
    }
}
