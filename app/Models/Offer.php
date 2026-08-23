<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Offer extends Model
{
    protected $fillable = [
        'supermarket_id', 'external_id', 'type', 'name', 'brand', 'match_key',
        'price', 'original_price', 'image_url', 'source_url', 'city',
        'is_active', 'captured_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'original_price' => 'decimal:2',
            'is_active' => 'boolean',
            'captured_at' => 'datetime',
        ];
    }

    public function supermarket(): BelongsTo
    {
        return $this->belongsTo(Supermarket::class);
    }
}
