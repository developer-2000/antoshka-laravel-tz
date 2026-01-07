@php
    $labels = [
        'pending' => 'Ожидает',
        'reserved' => 'Зарезервирован',
        'awaiting_restock' => 'Ожидает пополнения',
        'failed' => 'Ошибка',
    ];
    $label = $labels[$status] ?? $status;
    
    $bgColor = '';
    $textColor = '';
    switch($status) {
        case 'pending':
            $bgColor = '#fef3c7';
            $textColor = '#92400e';
            break;
        case 'reserved':
            $bgColor = '#d1fae5';
            $textColor = '#065f46';
            break;
        case 'awaiting_restock':
            $bgColor = '#dbeafe';
            $textColor = '#1e40af';
            break;
        case 'failed':
            $bgColor = '#fee2e2';
            $textColor = '#991b1b';
            break;
        default:
            $bgColor = '#f3f4f6';
            $textColor = '#1f2937';
            break;
    }
@endphp

<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" style="background-color: {{ $bgColor }}; color: {{ $textColor }};">
    {{ $label }}
</span>

