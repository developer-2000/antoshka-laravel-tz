<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\View\View;

/**
 * Контроллер для работы с дашбордом
 */
class DashboardController extends Controller
{
    /**
     * @param DashboardService $dashboardService Сервис для работы с данными дашборда
     */
    public function __construct(
        private DashboardService $dashboardService
    ) {
    }

    /**
     * Отобразить главную страницу дашборда
     *
     * @return View
     */
    public function index(): View
    {
        $data = $this->dashboardService->getDashboardData();

        return view('dashboard', $data);
    }
}

