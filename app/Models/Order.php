<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_RESERVED = 'reserved';
    const STATUS_AWAITING_RESTOCK = 'awaiting_restock';
    const STATUS_FAILED = 'failed';

    protected $fillable = [
        'sku',
        'qty',
        'status',
        'supplier_ref',
        'supplier_check_attempts',
    ];

    /**
     * Scope для получения заказов со статусом pending
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope для получения заказов со статусом reserved
     */
    public function scopeReserved($query)
    {
        return $query->where('status', self::STATUS_RESERVED);
    }

    /**
     * Scope для получения заказов со статусом awaiting_restock
     */
    public function scopeAwaitingRestock($query)
    {
        return $query->where('status', self::STATUS_AWAITING_RESTOCK);
    }

    /**
     * Scope для получения заказов со статусом failed
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

}
