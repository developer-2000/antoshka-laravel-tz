<?php

use App\Helpers\IconHelper;

if (!function_exists('icon')) {
    function icon($name, $class = null)
    {
        $defaultClasses = [
            'plus' => 'w-5 h-5',
            'arrow-left' => 'w-4 h-4',
            'arrowLeft' => 'w-4 h-4',
            'eye' => 'w-4 h-4',
            'copy' => 'w-4 h-4',
            'document' => 'w-6 h-6',
            'clock' => 'w-6 h-6',
            'check-circle' => 'w-6 h-6',
            'checkCircle' => 'w-6 h-6',
            'cube' => 'w-6 h-6',
            'hashtag' => 'w-5 h-5',
            'link' => 'w-5 h-5',
            'chart' => 'w-5 h-5',
            'calendar' => 'w-5 h-5',
            'success' => 'w-5 h-5',
        ];

        $class = $class ?? $defaultClasses[$name] ?? 'w-5 h-5';
        
        // Преобразуем имя в camelCase: check-circle -> checkCircle
        $parts = preg_split('/[-_]/', $name);
        $method = $parts[0];
        for ($i = 1; $i < count($parts); $i++) {
            $method .= ucfirst($parts[$i]);
        }
        
        // Если метод не найден, пробуем оригинальное имя
        if (!method_exists(IconHelper::class, $method)) {
            $method = $name;
        }

        if (method_exists(IconHelper::class, $method)) {
            return IconHelper::$method($class);
        }

        return '';
    }
}

