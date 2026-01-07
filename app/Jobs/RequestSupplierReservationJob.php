<?php

namespace App\Jobs;

use App\Exceptions\SupplierException;
use App\Jobs\CheckSupplierStatusJob;
use App\Models\Order;
use App\Services\SupplierService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RequestSupplierReservationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $orderId;
    public $tries = 3;
    public $timeout = 90;

    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * Обработка запроса резервирования у поставщика
     * 
     * Процесс:
     * 1. Получает заказ по ID
     * 2. Отправляет запрос к поставщику через SupplierService::requestReservation()
     * 3. Обрабатывает ответ поставщика:
     *    - Если accepted: true - сохраняет supplier_ref и запускает CheckSupplierStatusJob через 15 секунд
     *    - Если accepted: false - обновляет статус заказа на "failed"
     * 4. При ошибках (SupplierException) - обновляет статус на "failed" и пробрасывает исключение
     */
    public function handle(SupplierService $supplierService): void
    {
        try {
            // 1. Получаем заказ с блокировкой для предотвращения одновременной обработки
            $order = Order::lockForUpdate()->find($this->orderId);

            // 2. Проверяем что заказ существует
            if (!$order) {
                Log::error('Order not found in RequestSupplierReservationJob', [
                    'order_id' => $this->orderId,
                ]);
                return;
            }

            // 2.1. Проверяем, не был ли уже отправлен запрос к поставщику (с блокировкой)
            if (!empty($order->supplier_ref)) {
                return;
            }

            // 3. Отправляем запрос к поставщику через SupplierService
            $response = $supplierService->requestReservation($order->sku, $order->qty);

            Log::info('Supplier reservation accepted', [
                'order_id' => $order->id,
                'ref' => $response['ref'],
            ]);

            // 5. Обрабатываем ответ поставщика
            if ($response['accepted'] === true) {
                // 6. Поставщик принял заказ - сохраняем ссылку и запускаем проверку статуса
                // Используем транзакцию с блокировкой для предотвращения дублирования
                DB::transaction(function () use ($order, $response) {
                    $order->lockForUpdate();
                    $order->refresh();
                    
                    // Двойная проверка после блокировки
                    if (!empty($order->supplier_ref)) {
                        return;
                    }
                    
                    $order->supplier_ref = $response['ref'];
                    $order->save();
                });
                
                $order->refresh();
                
                // Запускаем проверку статуса поставки через 15 секунд (только если ref установлен)
                if (!empty($order->supplier_ref)) {
                    CheckSupplierStatusJob::dispatch($order->id)->delay(15);
                }
            } else {
                // 7. Поставщик отклонил заказ - обновляем статус на "failed"
                $order->status = Order::STATUS_FAILED;
                $order->save();

                Log::info('Supplier reservation rejected', [
                    'order_id' => $order->id,
                ]);
            }
        } catch (SupplierException $e) {
            // 8. Обработка ошибок поставщика (таймаут, сетевая ошибка и т.д.)
            Log::error('SupplierException in RequestSupplierReservationJob', [
                'order_id' => $this->orderId,
                'error' => $e->getMessage(),
            ]);

            // Обновляем статус заказа на "failed" при ошибке
            $order = Order::find($this->orderId);
            if ($order) {
                $order->status = Order::STATUS_FAILED;
                $order->save();
            }

            throw $e;
        } catch (\Exception $e) {
            // 9. Обработка общих ошибок
            Log::error('Error in RequestSupplierReservationJob', [
                'order_id' => $this->orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('RequestSupplierReservationJob failed', [
            'order_id' => $this->orderId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        $order = Order::find($this->orderId);
        if ($order) {
            $order->status = Order::STATUS_FAILED;
            $order->save();
        }
    }
}
