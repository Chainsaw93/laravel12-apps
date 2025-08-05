<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\InvoiceReturnItem;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'product_id',
        'unit_id',
        'quantity',
        'price',
        'currency_price',
        'total',
        'cost',
        'total_cost',
        'returned_quantity',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'currency_price' => 'decimal:2',
        'total' => 'decimal:2',
        'cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function returnItems(): HasMany
    {
        return $this->hasMany(InvoiceReturnItem::class);
    }
}
