<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('orders', fn ($user) => in_array($user->role, ['admin', 'cashier', 'waiter']));
Broadcast::channel('inventory', fn ($user) => in_array($user->role, ['admin', 'cashier']));
Broadcast::channel('security', fn ($user) => $user->role === 'admin');
