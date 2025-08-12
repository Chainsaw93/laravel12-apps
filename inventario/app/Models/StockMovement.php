<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\MovementType;

/**
 * Represents a movement of stock. The unit_cost attribute always stores the
 * value per base unit in the currency specified by the currency field.
 */
class StockMovement extends Model
{
    protected $fillable = [
        'stock_id',
        'type',
        'quantity',
        'unit_cost',
        'currency',
        'exchange_rate_id',
        'reason',
        'description',
        'user_id',
    ];

    protected $casts = [
        'type' => MovementType::class,
        'unit_cost' => 'decimal:2',
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
