<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mesa_id',
        'metodo_pago_id',
        'client_id',
        'cash_register_id',
        'order_number',
        'total_amount',
        'status',
        'payment_method',
        'payment_status',
        'type',
        'notes',
        'cancellation_reason'
    ];

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

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'metodo_pago_id' => 'integer',
            'mesa_id' => 'integer',
            'client_id' => 'integer',
            'cash_register_id' => 'integer',
        ];
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function canBeModified(): bool
    {
        return !in_array($this->status, ['completed', 'cancelled']);
    }

    public function canBeCancelled(): bool
    {
        return !in_array($this->status, ['completed', 'cancelled']);
    }

    public function canBePaid(): bool
    {
        return !in_array($this->status, ['cancelled', 'completed']) && $this->payment_status !== 'paid';
    }

    public function markAsPaid($paymentMethod): void
    {
        $this->update([
            'payment_status' => 'paid',
            'payment_method' => $paymentMethod,
        ]);
    }

    public function cancel(string $reason): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }

        $this->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
            'payment_status' => $this->payment_status === 'paid' ? 'refunded' : 'pending',
        ]);

        return true;
    }
}
