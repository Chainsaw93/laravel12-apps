<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'product_id',
        'quantity',
        'price',
        'currency_price',
        'total',
        'cost',
        'total_cost',
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
}
