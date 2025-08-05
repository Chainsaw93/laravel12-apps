<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\MovementType;

class StockMovement extends Model
{
    protected $fillable = [
        'stock_id',
        'type',
        'quantity',
        'purchase_price',
        'currency',
        'exchange_rate_id',
        'reason',
        'description',
        'user_id',
    ];

    protected $casts = [
        'type' => MovementType::class,
        'purchase_price' => 'decimal:2',
    ];

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function exchangeRate(): BelongsTo
    {
        return $this->belongsTo(ExchangeRate::class);
    }
}
