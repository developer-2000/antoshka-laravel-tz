<?php

namespace App\Services;

use App\Exceptions\SupplierException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupplierService
{
    private string $baseUrl;

    public function __construct()
    {
        // Используем SUPPLIER_API_URL из конфига (из .env или docker-compose.yml)
        $supplierUrl = config('services.supplier.url');
        
        if ($supplierUrl && $supplierUrl !== 'http://localhost/api/supplier') {
            $this->baseUrl = rtrim($supplierUrl, '/');
        } else {
            // Используем APP_URL из конфига для локального endpoint поставщика
            $appUrl = config('app.url', 'http://localhost');
            $this->baseUrl = rtrim($appUrl, '/') . '/api/supplier';
        }
    }

    public function requestReservation(string $sku, int $qty): array
    {
        try {
            $response = Http::timeout(30)
                ->post($this->baseUrl . '/reserve', [
                    'sku' => $sku,
                    'qty' => $qty,
                ]);

            if ($response->failed()) {
                throw new SupplierException('Supplier API returned error: ' . $response->status());
            }

            $data = $response->json();

            if (!isset($data['accepted']) || !isset($data['ref'])) {
                throw new SupplierException('Invalid response format from supplier API');
            }

            return [
                'accepted' => (bool) $data['accepted'],
                'ref' => (string) $data['ref'],
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Network error when contacting supplier', [
                'sku' => $sku,
                'qty' => $qty,
                'error' => $e->getMessage(),
            ]);
            throw new SupplierException('Network error: ' . $e->getMessage());
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('Request error when contacting supplier', [
                'sku' => $sku,
                'qty' => $qty,
                'error' => $e->getMessage(),
            ]);
            throw new SupplierException('Request error: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Unexpected error when contacting supplier', [
                'sku' => $sku,
                'qty' => $qty,
                'error' => $e->getMessage(),
            ]);
            throw new SupplierException('Unexpected error: ' . $e->getMessage());
        }
    }

    public function checkStatus(string $ref): string
    {
        try {
            $response = Http::timeout(30)
                ->get($this->baseUrl . '/status/' . $ref);

            if ($response->failed()) {
                throw new SupplierException('Supplier API returned error: ' . $response->status());
            }

            $data = $response->json();

            if (!isset($data['status'])) {
                throw new SupplierException('Invalid response format from supplier API');
            }

            $status = $data['status'];
            if (!in_array($status, ['ok', 'fail', 'delayed'])) {
                throw new SupplierException('Invalid status value: ' . $status);
            }

            return $status;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Network error when checking supplier status', [
                'ref' => $ref,
                'error' => $e->getMessage(),
            ]);
            throw new SupplierException('Network error: ' . $e->getMessage());
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('Request error when checking supplier status', [
                'ref' => $ref,
                'error' => $e->getMessage(),
            ]);
            throw new SupplierException('Request error: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Unexpected error when checking supplier status', [
                'ref' => $ref,
                'error' => $e->getMessage(),
            ]);
            throw new SupplierException('Unexpected error: ' . $e->getMessage());
        }
    }
}

