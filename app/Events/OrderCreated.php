<?php

namespace App\Events;

use App\Jobs\ReserveInventoryJob;
use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Явная обработка события - резервирование товара
     */
    public function handle(): void
    {
        ReserveInventoryJob::dispatch($this->order->id);
    }
}
