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
        'description',
        'user_id',
    ];

    protected $casts = [
        'type' => MovementType::class,
    ];

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
