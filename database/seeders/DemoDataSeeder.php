<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Client;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CashRegister;
use App\Models\CashMovement;
use App\Models\Mesa;
use App\Models\MetodoPago;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar datos demo anteriores
        OrderItem::whereIn('order_id', Order::where('order_number', 'like', 'DEMO-%')->pluck('id'))->delete();
        Order::where('order_number', 'like', 'DEMO-%')->delete();
        CashRegister::where('status', 'open')->where('opening_balance', 200)->delete();

        $admin = User::where('email', 'admin@crispy.com')->first();

        // Usuarios adicionales
        $cajero = User::updateOrCreate(
            ['email' => 'cajero@crispy.com'],
            ['name' => 'Carlos Cajero', 'password' => bcrypt('password'), 'role' => 'cashier', 'email_verified_at' => now()]
        );
        $mozo = User::updateOrCreate(
            ['email' => 'mozo@crispy.com'],
            ['name' => 'Miguel Mozo', 'password' => bcrypt('password'), 'role' => 'waiter', 'email_verified_at' => now()]
        );

        // Clientes de prueba
        $clientes = [
            ['name' => 'Ana López', 'phone' => '71234567', 'document_number' => '12345678', 'points' => 50],
            ['name' => 'Pedro García', 'phone' => '72345678', 'document_number' => '87654321', 'points' => 120],
            ['name' => 'María Rodríguez', 'phone' => '73456789', 'document_number' => '11223344', 'points' => 30],
        ];
        foreach ($clientes as $data) {
            Client::updateOrCreate(['document_number' => $data['document_number']], $data);
        }

        // Productos faltantes: Combos Familiares
        $catCombo = Category::where('slug', 'combos-familiares')->first();
        if ($catCombo) {
            $combo = Product::updateOrCreate(
                ['name' => 'Combo Familiar 4 Pers.'],
                ['category_id' => $catCombo->id, 'slug' => Str::slug('Combo Familiar 4 Pers.'), 'description' => '1 pollo entero + papas grandes + ensalada + 4 bebidas 500ml', 'is_active' => true]
            );
            ProductVariant::updateOrCreate(['product_id' => $combo->id, 'name' => 'Completo'], ['price' => 89.00, 'stock' => null]);
            ProductVariant::updateOrCreate(['product_id' => $combo->id, 'name' => 'Sin Bebidas'], ['price' => 69.00, 'stock' => null]);

            $combo2 = Product::updateOrCreate(
                ['name' => 'Combo Duo'],
                ['category_id' => $catCombo->id, 'slug' => Str::slug('Combo Duo'), 'description' => '1/2 pollo + papas medianas + 2 bebidas 500ml', 'is_active' => true]
            );
            ProductVariant::updateOrCreate(['product_id' => $combo2->id, 'name' => 'Completo'], ['price' => 52.00, 'stock' => null]);
        }

        // Guarniciones
        $catGuar = Category::where('slug', 'guarniciones')->first();
        if ($catGuar) {
            $guar = Product::updateOrCreate(
                ['name' => 'Papas Fritas'],
                ['category_id' => $catGuar->id, 'slug' => Str::slug('Papas Fritas'), 'description' => 'Papas fritas crocantes', 'is_active' => true]
            );
            ProductVariant::updateOrCreate(['product_id' => $guar->id, 'name' => 'Pequeñas'], ['price' => 8.00, 'stock' => null]);
            ProductVariant::updateOrCreate(['product_id' => $guar->id, 'name' => 'Grandes'], ['price' => 14.00, 'stock' => null]);

            $ensalada = Product::updateOrCreate(
                ['name' => 'Ensalada Clásica'],
                ['category_id' => $catGuar->id, 'slug' => Str::slug('Ensalada Clásica'), 'description' => 'Lechuga, tomate y cebolla', 'is_active' => true]
            );
            ProductVariant::updateOrCreate(['product_id' => $ensalada->id, 'name' => 'Personal'], ['price' => 6.00, 'stock' => null]);

            $arroz = Product::updateOrCreate(
                ['name' => 'Arroz Chaufa'],
                ['category_id' => $catGuar->id, 'slug' => Str::slug('Arroz Chaufa'), 'description' => 'Arroz chaufa casero', 'is_active' => true]
            );
            ProductVariant::updateOrCreate(['product_id' => $arroz->id, 'name' => 'Porción'], ['price' => 10.00, 'stock' => null]);
        }

        // Caja abierta
        $caja = CashRegister::updateOrCreate(
            ['status' => 'open'],
            ['user_id' => $admin->id, 'opened_at' => now()->subHours(3), 'opening_balance' => 200.00]
        );

        // Pedidos de prueba con diferentes estados
        $mesas = Mesa::all();
        $metodosPago = MetodoPago::all();
        $clientes = Client::all();
        $variantes = ProductVariant::all();
        $users = [$admin, $cajero, $mozo];

        $pedidos = [
            ['status' => 'pending', 'payment_status' => 'pending', 'payment_method' => 'cash', 'type' => 'dine_in', 'mesa_id' => $mesas[0]->id, 'user' => $cajero, 'client' => null, 'items' => [['variant' => '1/4 de Pollo', 'qty' => 2], ['variant' => '500ml', 'qty' => 2]]],
            ['status' => 'cooking', 'payment_status' => 'pending', 'payment_method' => 'yape', 'type' => 'dine_in', 'mesa_id' => $mesas[1]->id, 'user' => $cajero, 'client' => $clientes[0], 'items' => [['variant' => 'Mostrito', 'qty' => 1], ['variant' => 'Papas Fritas - Grandes', 'qty' => 1]]],
            ['status' => 'ready', 'payment_status' => 'paid', 'payment_method' => 'cash', 'type' => 'dine_in', 'mesa_id' => $mesas[2]->id, 'user' => $cajero, 'client' => null, 'items' => [['variant' => 'Combo Familiar 4 Pers. - Completo', 'qty' => 1]]],
            ['status' => 'completed', 'payment_status' => 'paid', 'payment_method' => 'card', 'type' => 'take_out', 'mesa_id' => null, 'user' => $cajero, 'client' => $clientes[1], 'items' => [['variant' => '1/2 de Pollo', 'qty' => 1], ['variant' => '1.5 Litros', 'qty' => 1]]],
            ['status' => 'cancelled', 'payment_status' => 'refunded', 'payment_method' => 'cash', 'type' => 'dine_in', 'mesa_id' => $mesas[3]->id, 'user' => $mozo, 'client' => null, 'items' => [['variant' => 'Duo Broster', 'qty' => 1]], 'cancellation_reason' => 'Cliente se retiró del local'],
            ['status' => 'pending', 'payment_status' => 'pending', 'payment_method' => 'cash', 'type' => 'dine_in', 'mesa_id' => $mesas[4]->id, 'user' => $cajero, 'client' => $clientes[2], 'items' => [['variant' => 'Pecho', 'qty' => 2], ['variant' => 'Pierna', 'qty' => 1], ['variant' => 'Ala', 'qty' => 3], ['variant' => 'Arroz Chaufa', 'qty' => 1]]],
        ];

        foreach ($pedidos as $i => $p) {
            $total = 0;
            $itemsData = [];
            foreach ($p['items'] as $itemDef) {
                $variant = $variantes->first(fn($v) => str_contains(strtolower($v->product->name . ' - ' . $v->name), strtolower($itemDef['variant'])));
                if (!$variant) continue;
                $subtotal = $variant->price * $itemDef['qty'];
                $total += $subtotal;
                $itemsData[] = [
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $itemDef['qty'],
                    'price' => $variant->price,
                    'subtotal' => $subtotal,
                ];
            }

            $orderData = [
                'user_id' => $p['user']->id,
                'order_number' => 'DEMO-' . now()->format('ymd') . '-' . strtoupper(substr(uniqid('', true), -6)),
                'total_amount' => $total,
                'status' => $p['status'],
                'payment_status' => $p['payment_status'],
                'payment_method' => $p['payment_method'],
                'type' => $p['type'],
                'mesa_id' => $p['mesa_id'],
                'client_id' => $p['client']?->id,
                'created_at' => now()->subHours(4 - $i)->subMinutes(rand(10, 50)),
            ];
            if (isset($p['cancellation_reason'])) {
                $orderData['cancellation_reason'] = $p['cancellation_reason'];
            }

            $order = Order::create($orderData);

            foreach ($itemsData as $item) {
                OrderItem::create(array_merge($item, ['order_id' => $order->id]));
            }
        }

        echo "Datos de prueba insertados correctamente.\n";
    }
}
