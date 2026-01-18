<?php

namespace Tests\Unit\Services;

use App\Services\BaseService;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class BaseServiceTest extends TestCase
{
    public function test_validate_required_throws_exception_for_missing_fields(): void
    {
        $service = new class extends BaseService
        {
            public function validate(array $data): void
            {
                $this->validateRequired($data, ['name', 'email']);
            }
        };

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required fields');

        $service->validate(['name' => 'Test']);
    }

    public function test_validate_required_passes_with_all_fields(): void
    {
        $service = new class extends BaseService
        {
            public function validate(array $data): void
            {
                $this->validateRequired($data, ['name', 'email']);
            }
        };

        // Should not throw
        $service->validate([
            'name' => 'Test',
            'email' => 'test@example.com',
        ]);

        $this->assertTrue(true);
    }

    public function test_handle_error_logs_and_throws(): void
    {
        Log::spy();

        $service = new class extends BaseService
        {
            public function triggerError(): void
            {
                $this->handleError(new \Exception('Test error'), 'Custom message');
            }
        };

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Custom message');

        try {
            $service->triggerError();
        } catch (\RuntimeException $e) {
            Log::shouldHaveReceived('error')->once();
            throw $e;
        }
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
