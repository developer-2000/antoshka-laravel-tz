<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Создание заказа') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form id="orderForm" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="sku" :value="__('Артикул товара (SKU)')" />
                            <x-text-input id="sku" class="block mt-1 w-full" type="text" name="sku" required autofocus />
                            <div id="sku-error" class="mt-2 text-sm text-red-600"></div>
                            <x-input-error :messages="$errors->get('sku')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="qty" :value="__('Количество')" />
                            <x-text-input id="qty" class="block mt-1 w-full" type="number" name="qty" min="1" required />
                            <div id="qty-error" class="mt-2 text-sm text-red-600"></div>
                            <x-input-error :messages="$errors->get('qty')" class="mt-2" />
                        </div>

                        <div id="message" class="hidden"></div>

                        <!-- Блок визуализации процесса обработки заказа -->
                        <div id="processVisualization" class="hidden mt-6 p-6 bg-gray-50 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-semibold mb-4">Процесс обработки заказа</h3>

                            <div class="space-y-3">
                                <!-- Этап 1: Создание заказа -->
                                <div id="step-order-created" class="line-status p-3 bg-white rounded border">
                                    <div class="w-6 h-6 rounded-full border-2 border-gray-300 flex items-center justify-center flex-shrink-0">
                                        <span class="text-xs">1</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium">Создание заказа</div>
                                        <div class="text-sm text-gray-500">OrderCreated::dispatch($order)</div>
                                    </div>
                                    <div class="text-sm text-gray-400 flex-shrink-0 whitespace-nowrap">Ожидание...</div>
                                </div>

                                <!-- Этап 2: Обработка события -->
                                <div id="step-event-handled" class="line-status p-3 bg-white rounded border opacity-50">
                                    <div class="w-6 h-6 rounded-full border-2 border-gray-300 flex items-center justify-center flex-shrink-0">
                                        <span class="text-xs">2</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium">Обработка события</div>
                                        <div class="text-sm text-gray-500">OrderCreated::handle() → ReserveInventoryJob</div>
                                    </div>
                                    <div class="text-sm text-gray-400 flex-shrink-0 whitespace-nowrap">Ожидание...</div>
                                </div>

                                <!-- Этап 3: Резервирование -->
                                <div id="step-reservation" class="line-status p-3 bg-white rounded border opacity-50">
                                    <div class="w-6 h-6 rounded-full border-2 border-gray-300 flex items-center justify-center flex-shrink-0">
                                        <span class="text-xs">3</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium">Резервирование товара</div>
                                        <div class="text-sm text-gray-500">ReserveInventoryJob::handle()</div>
                                    </div>
                                    <div class="text-sm text-gray-400 flex-shrink-0 whitespace-nowrap">Ожидание...</div>
                                </div>

                                <!-- Этап 4: Запрос поставщику (если нужно) -->
                                <div id="step-supplier" class="hidden line-status p-3 bg-white rounded border opacity-50">
                                    <div class="w-6 h-6 rounded-full border-2 border-gray-300 flex items-center justify-center flex-shrink-0">
                                        <span class="text-xs">4</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium">Запрос к поставщику</div>
                                        <div class="text-sm text-gray-500">RequestSupplierReservationJob</div>
                                    </div>
                                    <div class="text-sm text-gray-400 flex-shrink-0 whitespace-nowrap">Ожидание...</div>
                                </div>
                            </div>

                            <!-- Текущий статус заказа -->
                            <div class="mt-4 p-4 bg-blue-50 rounded border border-blue-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm text-gray-600">Текущий статус заказа:</div>
                                        <div id="current-status" class="text-lg font-semibold text-blue-700">pending</div>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        ID: <span id="order-id">-</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Логи процесса -->
                            <div class="mt-4">
                                <div class="text-sm font-medium mb-2">Логи процесса:</div>
                                <div id="process-logs" class="bg-gray-900 text-green-400 p-4 rounded font-mono text-xs overflow-y-auto" style="height: 300px;">
                                    <div class="text-gray-500">Ожидание начала обработки...</div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <a id="cancelBtn" href="{{ route('orders.index') }}" class="text-gray-600 hover:text-gray-900">
                                Отмена
                            </a>
                            <x-primary-button id="submitBtn">
                                {{ __('Создать заказ') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('orderForm');
            const submitBtn = document.getElementById('submitBtn');
            const skuError = document.getElementById('sku-error');
            const qtyError = document.getElementById('qty-error');

            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                skuError.textContent = '';
                qtyError.textContent = '';

                const skuValue = document.getElementById('sku').value.trim();
                const qtyValue = parseInt(document.getElementById('qty').value);

                let isValid = true;

                if (!skuValue) {
                    skuError.textContent = 'Артикул обязателен для заполнения';
                    isValid = false;
                }

                if (!qtyValue || qtyValue < 1) {
                    qtyError.textContent = 'Количество должно быть не менее 1';
                    isValid = false;
                }

                if (!isValid) {
                    return;
                }

                submitBtn.disabled = true;
                submitBtn.textContent = 'Создание...';

                const formData = {
                    sku: skuValue,
                    qty: qtyValue,
                };

                try {
                    const response = await window.orderApi.create(formData);
                    const orderId = response.data.id;

                    window.showSuccess('Заказ успешно создан');

                    // Показываем визуализацию процесса
                    const processVisualization = document.getElementById('processVisualization');
                    processVisualization.classList.remove('hidden');

                    // Обновляем информацию о заказе
                    document.getElementById('order-id').textContent = orderId;
                    document.getElementById('current-status').textContent = 'pending';

                    // Отмечаем первый этап как выполненный
                    markStepCompleted('step-order-created', 'Заказ создан, событие отправлено в очередь');

                    // Скрываем кнопки создания заказа и отмены
                    submitBtn.style.display = 'none';
                    const cancelBtn = document.getElementById('cancelBtn');
                    if (cancelBtn) {
                        cancelBtn.style.display = 'none';
                    }

                    // Начинаем отслеживание статуса заказа
                    startOrderTracking(orderId);

                } catch (error) {
                    const errors = window.handleApiError(error);

                    if (errors) {
                        if (errors.sku) {
                            skuError.textContent = Array.isArray(errors.sku) ? errors.sku[0] : errors.sku;
                        }
                        if (errors.qty) {
                            qtyError.textContent = Array.isArray(errors.qty) ? errors.qty[0] : errors.qty;
                        }
                    }
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Создать заказ';
                }
            });

            // Функция для отметки этапа как выполненного
            function markStepCompleted(stepId, message) {
                const step = document.getElementById(stepId);
                if (!step) return;

                // Сбрасываем все возможные состояния (активное, ошибка, неактивное)
                step.classList.remove('opacity-50', 'bg-yellow-50', 'border-yellow-300', 'bg-red-50', 'border-red-300');
                step.classList.add('bg-green-50', 'border-green-300');

                const circle = step.querySelector('.w-6');
                if (circle) {
                    // Сбрасываем все возможные классы
                    circle.classList.remove('border-gray-300', 'border-yellow-500', 'bg-yellow-100', 'bg-red-500', 'border-red-500');
                    circle.classList.add('bg-green-500', 'border-green-500', 'text-white');
                    circle.innerHTML = '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>';
                }

                // Ищем статус div справа (последний .text-sm в этапе, который не внутри .flex-1)
                let statusDiv = step.querySelector('.flex-1 + .text-sm');
                if (!statusDiv) {
                    // Если не нашли, ищем последний .text-sm в этапе
                    const allTextSm = step.querySelectorAll('.text-sm');
                    if (allTextSm.length > 0) {
                        statusDiv = allTextSm[allTextSm.length - 1];
                    }
                }
                if (statusDiv) {
                    statusDiv.textContent = '✓ Выполнено';
                    statusDiv.className = 'text-sm text-green-600 font-medium';
                }

                addLog(message);
            }

            // Функция для отметки этапа как активного
            function markStepActive(stepId, message) {
                const step = document.getElementById(stepId);
                if (!step) return;

                step.classList.remove('opacity-50');
                step.classList.add('bg-yellow-50', 'border-yellow-300');

                const circle = step.querySelector('.w-6');
                if (circle) {
                    circle.classList.remove('border-gray-300');
                    circle.classList.add('border-yellow-500', 'bg-yellow-100');
                }

                // Ищем статус div справа (последний .text-sm в этапе, который не внутри .flex-1)
                let statusDiv = step.querySelector('.flex-1 + .text-sm');
                if (!statusDiv) {
                    // Если не нашли, ищем последний .text-sm в этапе
                    const allTextSm = step.querySelectorAll('.text-sm');
                    if (allTextSm.length > 0) {
                        statusDiv = allTextSm[allTextSm.length - 1];
                    }
                }
                if (statusDiv) {
                    statusDiv.textContent = '⏳ В процессе...';
                    statusDiv.className = 'text-sm text-yellow-600 font-medium';
                }

                addLog(message);
            }

            // Функция для отметки этапа как завершенного с ошибкой
            function markStepFailed(stepId, message) {
                const step = document.getElementById(stepId);
                if (!step) return;

                // Сбрасываем все возможные состояния
                step.classList.remove('opacity-50', 'bg-yellow-50', 'border-yellow-300', 'bg-green-50', 'border-green-300');
                step.classList.add('bg-red-50', 'border-red-300');

                const circle = step.querySelector('.w-6');
                if (circle) {
                    circle.classList.remove('border-gray-300', 'border-yellow-500', 'bg-yellow-100', 'bg-green-500', 'border-green-500');
                    circle.classList.add('bg-red-500', 'border-red-500', 'text-white');
                    circle.innerHTML = '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>';
                }

                // Ищем статус div справа (последний .text-sm в этапе, который не внутри .flex-1)
                let statusDiv = step.querySelector('.flex-1 + .text-sm');
                if (!statusDiv) {
                    // Если не нашли, ищем последний .text-sm в этапе
                    const allTextSm = step.querySelectorAll('.text-sm');
                    if (allTextSm.length > 0) {
                        statusDiv = allTextSm[allTextSm.length - 1];
                    }
                }
                if (statusDiv) {
                    statusDiv.textContent = '✗ Ошибка';
                    statusDiv.className = 'text-sm text-red-600 font-medium';
                }

                addLog(message);
            }

            // Функция для добавления лога
            function addLog(message) {
                const logsDiv = document.getElementById('process-logs');
                if (!logsDiv) return;

                const timestamp = new Date().toLocaleTimeString('ru-RU');
                const logEntry = document.createElement('div');
                logEntry.className = 'mb-1';
                logEntry.innerHTML = `<span class="text-gray-500">[${timestamp}]</span> ${message}`;

                logsDiv.appendChild(logEntry);
                logsDiv.scrollTop = logsDiv.scrollHeight;
            }

            // Функция для отслеживания статуса заказа
            function startOrderTracking(orderId) {
                let checkCount = 0;
                const maxChecks = 60; // Максимум 60 проверок (около 1 минуты)

                addLog('Начато отслеживание статуса заказа...');

                const checkStatus = async () => {
                    try {
                        const response = await window.orderApi.get(orderId);
                        const order = response.data;

                        // Обновляем текущий статус
                        const statusDiv = document.getElementById('current-status');
                        if (statusDiv) {
                            statusDiv.textContent = order.status;

                            // Меняем цвет в зависимости от статуса
                            statusDiv.className = 'text-lg font-semibold ';
                            switch(order.status) {
                                case 'pending':
                                    statusDiv.className += 'text-yellow-700';
                                    break;
                                case 'reserved':
                                    statusDiv.className += 'text-green-700';
                                    break;
                                case 'awaiting_restock':
                                    statusDiv.className += 'text-blue-700';
                                    break;
                                case 'failed':
                                    statusDiv.className += 'text-red-700';
                                    break;
                                default:
                                    statusDiv.className += 'text-gray-700';
                            }
                        }

                        // Отмечаем этапы в зависимости от статуса
                        if (order.status === 'pending') {
                            markStepActive('step-event-handled', 'Событие обрабатывается воркером очереди...');
                        } else if (order.status === 'reserved') {
                            // Все этапы завершены - помечаем их все как завершенные
                            // Сначала сбрасываем активное состояние, если было
                            const eventStep = document.getElementById('step-event-handled');
                            if (eventStep) {
                                eventStep.classList.remove('bg-yellow-50', 'border-yellow-300');
                            }

                            markStepCompleted('step-event-handled', 'Событие обработано');
                            markStepCompleted('step-reservation', 'Товар успешно зарезервирован');

                            // Если был этап поставщика, помечаем его как завершенный
                            const supplierStep = document.getElementById('step-supplier');
                            if (supplierStep && !supplierStep.classList.contains('hidden')) {
                                markStepCompleted('step-supplier', 'Поставщик подтвердил поставку');
                            }

                            addLog('✓ Процесс завершен успешно!');
                            return; // Останавливаем проверку
                        } else if (order.status === 'awaiting_restock') {
                            markStepCompleted('step-event-handled', 'Событие обработано');
                            markStepCompleted('step-reservation', 'Товара недостаточно, запрос к поставщику');
                            document.getElementById('step-supplier').classList.remove('hidden');
                            markStepActive('step-supplier', 'Запрос к поставщику отправлен...');

                            if (order.supplier_ref) {
                                addLog(`Получена ссылка поставщика: ${order.supplier_ref}`);
                                // Если есть supplier_ref, значит запрос принят, помечаем как активный
                                markStepActive('step-supplier', 'Ожидание подтверждения поставки...');
                            }
                        } else if (order.status === 'failed') {
                            // Помечаем пройденные этапы
                            markStepCompleted('step-event-handled', 'Событие обработано');

                            // Если был этап поставщика, значит резервирование прошло, но поставщик отказал
                            const supplierStep = document.getElementById('step-supplier');
                            if (supplierStep && !supplierStep.classList.contains('hidden')) {
                                markStepCompleted('step-reservation', 'Резервирование выполнено');
                                markStepFailed('step-supplier', 'Запрос к поставщику завершился ошибкой');
                            } else {
                                // Если этапа поставщика не было, значит ошибка в резервировании
                                markStepFailed('step-reservation', 'Резервирование завершилось ошибкой');
                            }

                            addLog('✗ Процесс завершен с ошибкой');
                            return; // Останавливаем проверку
                        }

                        checkCount++;
                        if (checkCount < maxChecks) {
                            setTimeout(checkStatus, 1000); // Проверяем каждую секунду
                        } else {
                            addLog('Превышено время ожидания отслеживания');
                        }
                    } catch (error) {
                        addLog(`Ошибка при проверке статуса: ${error.message}`);
                        checkCount++;
                        if (checkCount < maxChecks) {
                            setTimeout(checkStatus, 2000); // При ошибке проверяем реже
                        }
                    }
                };

                // Начинаем проверку через 1 секунду
                setTimeout(checkStatus, 1000);
            }
        });
    </script>
</x-app-layout>

