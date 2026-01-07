<?php

namespace Tests\Helpers;

use Illuminate\Support\Facades\Http;

class SupplierApiHelper
{
    public static function fakeReserveSuccess(string $ref = null): void
    {
        $ref = $ref ?? 'SUP-' . date('Ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

        Http::fake([
            '*/supplier/reserve' => Http::response([
                'accepted' => true,
                'ref' => $ref,
            ], 200),
        ]);
    }

    public static function fakeReserveRejected(): void
    {
        Http::fake([
            '*/supplier/reserve' => Http::response([
                'accepted' => false,
                'error' => 'Not available',
            ], 200),
        ]);
    }

    public static function fakeStatusOk(string $ref): void
    {
        Http::fake([
            "*/supplier/status/{$ref}" => Http::response([
                'status' => 'ok',
            ], 200),
        ]);
    }

    public static function fakeStatusFail(string $ref): void
    {
        Http::fake([
            "*/supplier/status/{$ref}" => Http::response([
                'status' => 'fail',
            ], 200),
        ]);
    }

    public static function fakeStatusDelayed(string $ref): void
    {
        Http::fake([
            "*/supplier/status/{$ref}" => Http::response([
                'status' => 'delayed',
            ], 200),
        ]);
    }

    public static function fakeStatusSequence(string $ref, array $statuses): void
    {
        $responses = array_map(function ($status) {
            return Http::response(['status' => $status], 200);
        }, $statuses);

        Http::fake([
            "*/supplier/status/{$ref}" => Http::sequence($responses),
        ]);
    }

    public static function fakeTimeout(): void
    {
        Http::fake([
            '*/supplier/*' => function () {
                return Http::response([], 0)->throw();
            },
        ]);
    }

    public static function fakeNetworkError(): void
    {
        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('Connection refused');
        });
    }

    public static function fakeInvalidResponse(): void
    {
        Http::fake([
            '*/supplier/*' => Http::response([
                'invalid' => 'response',
            ], 200),
        ]);
    }
}

