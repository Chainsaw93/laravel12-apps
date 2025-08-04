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
}
