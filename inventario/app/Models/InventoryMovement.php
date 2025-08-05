<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\MovementType;

class InventoryMovement extends Model
{
    protected $fillable = [
        'batch_id',
        'product_id',
        'warehouse_id',
        'movement_type',
        'quantity',
        'unit_cost_cup',
        'indirect_cost_unit',
        'currency',
        'exchange_rate_id',
        'total_cost_cup',
        'reference_id',
        'user_id',
    ];

    protected $casts = [
        'movement_type' => MovementType::class,
        'unit_cost_cup' => 'decimal:2',
        'indirect_cost_unit' => 'decimal:2',
        'total_cost_cup' => 'decimal:2',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function exchangeRate(): BelongsTo
    {
        return $this->belongsTo(ExchangeRate::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
