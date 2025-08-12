<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceReturnItem extends Model
{
    protected $fillable = [
        'invoice_return_id',
        'invoice_item_id',
        'quantity',
        'amount',
        'cost',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'cost' => 'decimal:4',
    ];

    public function invoiceReturn(): BelongsTo
    {
        return $this->belongsTo(InvoiceReturn::class, 'invoice_return_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class, 'invoice_item_id');
    }
}
