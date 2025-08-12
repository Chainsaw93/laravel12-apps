<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'category_id',
        'unit_id',
        'description',
        'image_path',
        'expiry_date',
        'price',
        'cost',
        'currency',
        'cost_cup',
        'cost_usd',
        'cost_mlc',
        'sku',
    ];

    protected $casts = [
        'cost' => 'decimal:4',
        'cost_cup' => 'decimal:4',
        'cost_usd' => 'decimal:4',
        'cost_mlc' => 'decimal:4',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function presentations(): HasMany
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function getConversionFactor(?int $unitId): float
    {
        if (!$unitId || $unitId === $this->unit_id) {
            return 1.0;
        }
        $conversion = $this->presentations()->where('unit_id', $unitId)->first();
        return $conversion?->conversion_factor ?? 1.0;
    }

    public function convertToBase(?int $unitId, float $quantity): float
    {
        return $quantity * $this->getConversionFactor($unitId);
    }
}
