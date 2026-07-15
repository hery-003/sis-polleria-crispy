<?php

namespace App\Policies;

use App\Models\CashRegister;
use App\Models\User;

class CashRegisterPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function view(User $user, CashRegister $cashRegister): bool
    {
        return $user->role === 'admin';
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, CashRegister $cashRegister): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, CashRegister $cashRegister): bool
    {
        return $user->role === 'admin';
    }
}
