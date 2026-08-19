<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Flyer extends Model
{
    use HasFactory;

    protected $fillable = ['supermarket_id', 'title', 'file_path', 'starts_at', 'ends_at', 'is_published'];

    protected function casts(): array
    {
        return ['starts_at' => 'date', 'ends_at' => 'date', 'is_published' => 'boolean'];
    }

    public function supermarket(): BelongsTo
    {
        return $this->belongsTo(Supermarket::class);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhereDate('starts_at', '<=', today()))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhereDate('ends_at', '>=', today()));
    }
}
