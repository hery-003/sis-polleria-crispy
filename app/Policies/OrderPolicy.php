<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'cashier', 'waiter']);
    }

    public function view(User $user, Order $order): bool
    {
        return in_array($user->role, ['admin', 'cashier', 'waiter']);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'cashier']);
    }

    public function update(User $user, Order $order): bool
    {
        if (! in_array($user->role, ['admin', 'cashier'])) {
            return false;
        }

        return ! in_array($order->status, ['completed', 'cancelled']);
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->role === 'admin' && ! in_array($order->status, ['completed', 'cancelled']);
    }
}
