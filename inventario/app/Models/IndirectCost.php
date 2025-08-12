<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndirectCost extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'purchase_id',
        'description',
        'amount_cup',
        'allocated',
    ];

    protected $casts = [
        'amount_cup' => 'decimal:4',
        'allocated' => 'boolean',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }
}
