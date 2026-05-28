<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    protected $fillable = [
        'order_id',
        'designation',
        'quantity',
        'unit_price',
        'vat_rate',
        'total_ttc',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Calcule le total TTC de la ligne
    public function calculateTotalTtc(): float
    {
        return round($this->quantity * $this->unit_price * (1 + $this->vat_rate / 100), 2);
    }
}
