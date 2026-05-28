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

        // Usuarios adicionales — total 10
        $usuariosExtra = [
            ['name' => 'Carlos Cajero', 'email' => 'cajero@crispy.com', 'role' => 'cashier'],
            ['name' => 'Miguel Mozo', 'email' => 'mozo@crispy.com', 'role' => 'waiter'],
            ['name' => 'Lucía Vásquez', 'email' => 'lucia@crispy.com', 'role' => 'cashier'],
            ['name' => 'Jorge Medina', 'email' => 'jorge@crispy.com', 'role' => 'waiter'],
            ['name' => 'Rosa Cárdenas', 'email' => 'rosa@crispy.com', 'role' => 'admin'],
            ['name' => 'Diego Ramos', 'email' => 'diego@crispy.com', 'role' => 'waiter'],
            ['name' => 'Carla Mendoza', 'email' => 'carla@crispy.com', 'role' => 'cashier'],
            ['name' => 'Pedro Huamán', 'email' => 'pedro@crispy.com', 'role' => 'waiter'],
            ['name' => 'Mónica Paredes', 'email' => 'monica@crispy.com', 'role' => 'cashier'],
        ];
        $users = collect([$admin]);
        foreach ($usuariosExtra as $data) {
            $users->push(User::updateOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'password' => bcrypt('password'), 'role' => $data['role'], 'email_verified_at' => now()]
            ));
        }
        $usersArray = $users->values();

        // Clientes de prueba — total 10
        $clientesData = [
            ['name' => 'Ana López', 'phone' => '71234567', 'document_number' => '12345678', 'points' => 50],
            ['name' => 'Pedro García', 'phone' => '72345678', 'document_number' => '87654321', 'points' => 120],
            ['name' => 'María Rodríguez', 'phone' => '73456789', 'document_number' => '11223344', 'points' => 30],
            ['name' => 'Luis Torres', 'phone' => '74567890', 'document_number' => '55667788', 'points' => 200],
            ['name' => 'Carmen Flores', 'phone' => '75678901', 'document_number' => '99887766', 'points' => 75],
            ['name' => 'José Castillo', 'phone' => '76789012', 'document_number' => '33445566', 'points' => 150],
            ['name' => 'Diana Herrera', 'phone' => '77890123', 'document_number' => '22334455', 'points' => 10],
            ['name' => 'Ricardo Silva', 'phone' => '78901234', 'document_number' => '66778899', 'points' => 90],
            ['name' => 'Katherine Ríos', 'phone' => '79012345', 'document_number' => '44556677', 'points' => 180],
            ['name' => 'Fernando Vega', 'phone' => '70123456', 'document_number' => '11122334', 'points' => 0],
        ];
        $clientes = [];
        foreach ($clientesData as $data) {
            $clientes[] = Client::updateOrCreate(['document_number' => $data['document_number']], $data);
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

        // Cajas registradoras — total 10 (1 abierta, 9 cerradas)
        $caja = CashRegister::updateOrCreate(
            ['status' => 'open'],
            ['user_id' => $admin->id, 'opened_at' => now()->subHours(3), 'opening_balance' => 200.00]
        );

        $cierres = [
            ['user_id' => $usersArray[1]->id, 'opening_balance' => 150.00, 'closing_balance' => 1850.50],
            ['user_id' => $usersArray[3]->id, 'opening_balance' => 200.00, 'closing_balance' => 2100.00],
            ['user_id' => $usersArray[1]->id, 'opening_balance' => 180.00, 'closing_balance' => 1650.75],
            ['user_id' => $usersArray[6]->id, 'opening_balance' => 250.00, 'closing_balance' => 2340.00],
            ['user_id' => $usersArray[4]->id, 'opening_balance' => 300.00, 'closing_balance' => 3200.00],
            ['user_id' => $usersArray[3]->id, 'opening_balance' => 175.00, 'closing_balance' => 1450.25],
            ['user_id' => $usersArray[1]->id, 'opening_balance' => 220.00, 'closing_balance' => 1980.00],
            ['user_id' => $usersArray[6]->id, 'opening_balance' => 190.00, 'closing_balance' => 1725.00],
            ['user_id' => $usersArray[4]->id, 'opening_balance' => 250.00, 'closing_balance' => 2780.50],
        ];
        foreach ($cierres as $i => $c) {
            CashRegister::create([
                'user_id' => $c['user_id'],
                'opened_at' => now()->subDays(9 - $i)->subHours(8),
                'closed_at' => now()->subDays(9 - $i)->subHours(1),
                'opening_balance' => $c['opening_balance'],
                'closing_balance' => $c['closing_balance'],
                'status' => 'closed',
            ]);
        }

        // Pedidos de prueba con diferentes estados — total 10
        $mesas = Mesa::all();
        $metodosPago = MetodoPago::all();
        $clientes = Client::all();
        $variantes = ProductVariant::all();

        $pedidos = [
            ['status' => 'pending', 'payment_status' => 'pending', 'payment_method' => 'cash', 'type' => 'dine_in', 'mesa_id' => $mesas[0]->id, 'user' => $usersArray[1], 'client' => null, 'items' => [['variant' => '1/4 de Pollo', 'qty' => 2], ['variant' => '500ml', 'qty' => 2]]],
            ['status' => 'cooking', 'payment_status' => 'pending', 'payment_method' => 'yape', 'type' => 'dine_in', 'mesa_id' => $mesas[1]->id, 'user' => $usersArray[1], 'client' => $clientes[0], 'items' => [['variant' => 'Mostrito', 'qty' => 1], ['variant' => 'Papas Fritas - Grandes', 'qty' => 1]]],
            ['status' => 'ready', 'payment_status' => 'paid', 'payment_method' => 'cash', 'type' => 'dine_in', 'mesa_id' => $mesas[2]->id, 'user' => $usersArray[1], 'client' => null, 'items' => [['variant' => 'Combo Familiar 4 Pers. - Completo', 'qty' => 1]]],
            ['status' => 'completed', 'payment_status' => 'paid', 'payment_method' => 'card', 'type' => 'take_out', 'mesa_id' => null, 'user' => $usersArray[1], 'client' => $clientes[1], 'items' => [['variant' => '1/2 de Pollo', 'qty' => 1], ['variant' => '1.5 Litros', 'qty' => 1]]],
            ['status' => 'cancelled', 'payment_status' => 'refunded', 'payment_method' => 'cash', 'type' => 'dine_in', 'mesa_id' => $mesas[3]->id, 'user' => $usersArray[2], 'client' => null, 'items' => [['variant' => 'Duo Broster', 'qty' => 1]], 'cancellation_reason' => 'Cliente se retiró del local'],
            ['status' => 'pending', 'payment_status' => 'pending', 'payment_method' => 'cash', 'type' => 'dine_in', 'mesa_id' => $mesas[4]->id, 'user' => $usersArray[1], 'client' => $clientes[2], 'items' => [['variant' => 'Pecho', 'qty' => 2], ['variant' => 'Pierna', 'qty' => 1], ['variant' => 'Ala', 'qty' => 3], ['variant' => 'Arroz Chaufa', 'qty' => 1]]],
            ['status' => 'completed', 'payment_status' => 'paid', 'payment_method' => 'yape', 'type' => 'dine_in', 'mesa_id' => $mesas[5]->id, 'user' => $usersArray[3], 'client' => $clientes[3], 'items' => [['variant' => 'Combo Duo - Completo', 'qty' => 1], ['variant' => 'Coca Cola - 500ml', 'qty' => 2]]],
            ['status' => 'cooking', 'payment_status' => 'pending', 'payment_method' => 'cash', 'type' => 'delivery', 'mesa_id' => null, 'user' => $usersArray[4], 'client' => $clientes[4], 'items' => [['variant' => '1 Pollo Entero', 'qty' => 1], ['variant' => '1.5 Litros', 'qty' => 2], ['variant' => 'Porción', 'qty' => 2]]],
            ['status' => 'ready', 'payment_status' => 'paid', 'payment_method' => 'card', 'type' => 'dine_in', 'mesa_id' => $mesas[6]->id, 'user' => $usersArray[5], 'client' => null, 'items' => [['variant' => 'Tequeños - 12 unidades', 'qty' => 1], ['variant' => '1/2 de Pollo', 'qty' => 1], ['variant' => 'Cerveza Cusqueña - Botella 630ml', 'qty' => 2]]],
            ['status' => 'completed', 'payment_status' => 'paid', 'payment_method' => 'transferencia', 'type' => 'delivery', 'mesa_id' => null, 'user' => $usersArray[6], 'client' => $clientes[5], 'items' => [['variant' => 'Combo Familiar 4 Pers. - Sin Bebidas', 'qty' => 1], ['variant' => 'Coca Cola - 1.5 Litros', 'qty' => 2], ['variant' => 'Ensalada Clásica - Personal', 'qty' => 2]]],
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
