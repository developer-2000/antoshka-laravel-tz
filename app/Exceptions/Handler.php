<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $levels = [
        //
    ];

    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            Log::error('Exception occurred', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        });
    }

    public function render($request, Throwable $e)
    {
        if ($request->is('api/*')) {
            if ($e instanceof ValidationException) {
                return response()->json([
                    'error' => 'Ошибка валидации',
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                    'code' => 422,
                ], 422);
            }

            if ($e instanceof ModelNotFoundException || $e instanceof OrderNotFoundException) {
                return response()->json([
                    'error' => 'Ресурс не найден',
                    'message' => $e->getMessage() ?: 'Запрашиваемый ресурс не существует',
                    'code' => 404,
                ], 404);
            }

            if ($e instanceof SupplierException) {
                return response()->json([
                    'error' => 'Ошибка интеграции с поставщиком',
                    'message' => $e->getMessage(),
                    'code' => 503,
                ], 503);
            }

            if ($e instanceof InsufficientInventoryException) {
                return response()->json([
                    'error' => 'Недостаточно товара на складе',
                    'message' => $e->getMessage(),
                    'code' => 409,
                ], 409);
            }

            return response()->json([
                'error' => 'Внутренняя ошибка сервера',
                'message' => $e->getMessage(),
                'code' => 500,
            ], 500);
        }

        return parent::render($request, $e);
    }
}
