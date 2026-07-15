<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable('user_id', 'mesa_id', 'metodo_pago_id', 'client_id', 'cash_register_id', 'order_number', 'total_amount', 'received_amount', 'change', 'status', 'payment_method', 'payment_status', 'type', 'notes', 'cancellation_reason')]
class Order extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mesa()
    {
        return $this->belongsTo(Mesa::class);
    }

    public function metodoPago()
    {
        return $this->belongsTo(MetodoPago::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'received_amount' => 'decimal:2',
            'change' => 'decimal:2',
            'metodo_pago_id' => 'integer',
            'mesa_id' => 'integer',
            'client_id' => 'integer',
            'cash_register_id' => 'integer',
        ];
    }
}
