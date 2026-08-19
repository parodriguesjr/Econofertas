<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supermarket extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'color', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function flyers(): HasMany
    {
        return $this->hasMany(Flyer::class);
    }
}
