<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@crispy.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Cajero Principal',
            'email' => 'cajero@crispy.com',
            'password' => Hash::make('cajero123'),
            'role' => 'cashier',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Mesero Principal',
            'email' => 'mesero@crispy.com',
            'password' => Hash::make('mesero123'),
            'role' => 'waiter',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}
