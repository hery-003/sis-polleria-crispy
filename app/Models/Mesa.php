<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable('name', 'number', 'capacity', 'is_active', 'reserved_at')]
#[Appends('status')]
class Mesa extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'reserved_at' => 'datetime',
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getStatusAttribute(): string
    {
        if (!$this->is_active) return 'inactive';
        
        $hasActiveOrder = $this->orders()
            ->whereIn('status', ['pending', 'cooking', 'ready'])
            ->exists();

        if ($hasActiveOrder) return 'occupied';
        
        if ($this->reserved_at && $this->reserved_at->isFuture()) return 'reserved';

        return 'free';
    }
}
