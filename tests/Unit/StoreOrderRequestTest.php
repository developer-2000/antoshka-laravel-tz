<?php

namespace Tests\Unit;

use App\Http\Requests\StoreOrderRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreOrderRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_data_passes_validation(): void
    {
        $request = new StoreOrderRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'sku' => 'TEST123',
            'qty' => 10,
        ], $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_sku_is_required(): void
    {
        $request = new StoreOrderRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'qty' => 10,
        ], $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('sku', $validator->errors()->toArray());
    }

    public function test_sku_must_be_string(): void
    {
        $request = new StoreOrderRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'sku' => 123,
            'qty' => 10,
        ], $rules);

        $this->assertFalse($validator->passes());
    }

    public function test_sku_max_length(): void
    {
        $request = new StoreOrderRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'sku' => str_repeat('A', 256),
            'qty' => 10,
        ], $rules);

        $this->assertFalse($validator->passes());
    }

    public function test_qty_is_required(): void
    {
        $request = new StoreOrderRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'sku' => 'TEST123',
        ], $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('qty', $validator->errors()->toArray());
    }

    public function test_qty_must_be_integer(): void
    {
        $request = new StoreOrderRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'sku' => 'TEST123',
            'qty' => 'not-integer',
        ], $rules);

        $this->assertFalse($validator->passes());
    }

    public function test_qty_must_be_min_1(): void
    {
        $request = new StoreOrderRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'sku' => 'TEST123',
            'qty' => 0,
        ], $rules);

        $this->assertFalse($validator->passes());

        $validator = Validator::make([
            'sku' => 'TEST123',
            'qty' => -1,
        ], $rules);

        $this->assertFalse($validator->passes());
    }
}

