<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'quantity_remaining',
        'unit_cost_cup',
        'currency',
        'indirect_cost',
        'total_cost_cup',
        'received_at',
    ];

    protected $casts = [
        'unit_cost_cup' => 'decimal:4',
        'indirect_cost' => 'decimal:4',
        'total_cost_cup' => 'decimal:4',
        'received_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }
}
