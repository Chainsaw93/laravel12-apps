<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\PaymentMethod;
use App\Models\{InvoiceReturn, InvoiceCancellation};

class Invoice extends Model
{
    protected $fillable = [
        'client_id',
        'warehouse_id',
        'user_id',
        'currency',
        'exchange_rate_id',
        'total_amount',
        'total_cost',
        'status',
        'payment_method',
    ];

    protected $casts = [
        'payment_method' => PaymentMethod::class,
        'total_amount' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function exchangeRate(): BelongsTo
    {
        return $this->belongsTo(ExchangeRate::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(InvoiceReturn::class);
    }

    public function cancellations(): HasMany
    {
        return $this->hasMany(InvoiceCancellation::class);
    }
}
