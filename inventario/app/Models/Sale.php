<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\PaymentMethod;

class Sale extends Model
{
    protected $fillable = [
        'warehouse_id',
        'product_id',
        'quantity',
        'price_per_unit',
        'payment_method',
        'currency',
        'exchange_rate_id',
        'user_id',
    ];

    protected $casts = [
        'payment_method' => PaymentMethod::class,
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function exchangeRate(): BelongsTo
    {
        return $this->belongsTo(ExchangeRate::class);
    }
}
