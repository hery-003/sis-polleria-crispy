<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable('user_id', 'opened_at', 'closed_at', 'opening_balance', 'closing_balance', 'status', 'notes')]
class CashRegister extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'opening_balance' => 'decimal:2',
            'closing_balance' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function movements()
    {
        return $this->hasMany(CashMovement::class);
    }
}
