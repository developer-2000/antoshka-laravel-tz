<?php

namespace Tests\Unit;

use App\Exceptions\SupplierException;
use App\Services\SupplierService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SupplierServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_reservation_success(): void
    {
        Http::fake([
            '*/supplier/reserve' => Http::response([
                'accepted' => true,
                'ref' => 'SUP-20240106001',
            ], 200),
        ]);

        $service = new SupplierService();
        $result = $service->requestReservation('TEST123', 10);

        $this->assertTrue($result['accepted']);
        $this->assertEquals('SUP-20240106001', $result['ref']);
    }

    public function test_request_reservation_failed(): void
    {
        Http::fake([
            '*/supplier/reserve' => Http::response([
                'accepted' => false,
            ], 200),
        ]);

        $service = new SupplierService();
        
        $this->expectException(SupplierException::class);
        $service->requestReservation('TEST123', 10);
    }

    public function test_check_status_ok(): void
    {
        Http::fake([
            '*/supplier/status/*' => Http::response([
                'status' => 'ok',
            ], 200),
        ]);

        $service = new SupplierService();
        $status = $service->checkStatus('SUP-20240106001');

        $this->assertEquals('ok', $status);
    }

    public function test_check_status_fail(): void
    {
        Http::fake([
            '*/supplier/status/*' => Http::response([
                'status' => 'fail',
            ], 200),
        ]);

        $service = new SupplierService();
        $status = $service->checkStatus('SUP-20240106001');

        $this->assertEquals('fail', $status);
    }

    public function test_check_status_delayed(): void
    {
        Http::fake([
            '*/supplier/status/*' => Http::response([
                'status' => 'delayed',
            ], 200),
        ]);

        $service = new SupplierService();
        $status = $service->checkStatus('SUP-20240106001');

        $this->assertEquals('delayed', $status);
    }

    public function test_request_reservation_handles_network_error(): void
    {
        Http::fake([
            '*/supplier/reserve' => Http::response([], 500),
        ]);

        $service = new SupplierService();
        
        $this->expectException(SupplierException::class);
        $service->requestReservation('TEST123', 10);
    }
}

