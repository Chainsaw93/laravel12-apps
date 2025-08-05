<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $fillable = [
        'currency',
        'rate_to_cup',
        'effective_date',
        'user_id',
    ];

    protected $casts = [
        'effective_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
