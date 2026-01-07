<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OrderCreatedListener implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    public $connection = 'redis';
    public $queue = 'default';

    /**
     * Обработка события OrderCreated
     * Явно вызывает метод handle() из события
     */
    public function handle(OrderCreated $event): void
    {
        $event->handle();
    }
}

