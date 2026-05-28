<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'document_number',
        'address',
        'points',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'points' => 'integer',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getTotalPurchasesAttribute()
    {
        return $this->orders()->where('status', 'completed')->sum('total_amount');
    }
}
