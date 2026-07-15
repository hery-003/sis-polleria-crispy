<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable('name', 'phone', 'email', 'document_number', 'address', 'points', 'is_active')]
class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'points' => 'integer',
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getTotalPurchasesAttribute()
    {
        if ($this->relationLoaded('orders')) {
            return $this->orders->where('status', 'completed')->sum('total_amount');
        }

        return $this->loadAggregate('orders', 'total_amount', 'sum', fn ($q) => $q->where('status', 'completed'))->orders_aggregate ?? 0;
    }
}
