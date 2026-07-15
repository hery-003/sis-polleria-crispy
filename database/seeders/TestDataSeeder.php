<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\Category;
use App\Models\Client;
use App\Models\Mesa;
use App\Models\MetodoPago;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        Model::unsetEventDispatcher();

        $categories = Category::factory()->count(10)->create();
        $this->command->info('✓ 10 categorías creadas');

        $clients = Client::factory()->count(10)->create();
        $this->command->info('✓ 10 clientes creados');

        $mesas = Mesa::factory()->count(10)->create();
        $this->command->info('✓ 10 mesas creadas');

        $metodos = MetodoPago::factory()->count(5)->create();
        $this->command->info('✓ 5 métodos de pago creados');

        $users = User::factory()->count(10)->create();
        $this->command->info('✓ 10 usuarios creados');

        $products = Product::factory()->count(10)->create(
            fn () => ['category_id' => $categories->random()->id]
        );
        $this->command->info('✓ 10 productos creados');

        $variants = ProductVariant::factory()->count(10)->create(
            fn () => ['product_id' => $products->random()->id]
        );
        $this->command->info('✓ 10 variantes creadas');

        $cashRegisters = CashRegister::factory()->count(7)->create(
            fn () => ['user_id' => $users->random()->id]
        );
        $closedRegisters = CashRegister::factory()->count(3)->closed()->create(
            fn () => ['user_id' => $users->random()->id]
        );
        $allRegisters = $cashRegisters->concat($closedRegisters);
        $this->command->info('✓ 10 cajas creadas');

        CashMovement::factory()->count(10)->create(fn () => [
            'cash_register_id' => $allRegisters->random()->id,
            'user_id' => $users->random()->id,
        ]);
        $this->command->info('✓ 10 movimientos de caja creados');

        $orderTypes = ['dine_in', 'take_out', 'delivery'];
        $orderStatuses = ['pending', 'cooking', 'ready', 'completed', 'cancelled'];
        $paymentMethods = ['cash', 'card', 'yape', 'plin'];
        $paymentStatuses = ['pending', 'paid', 'refunded'];

        for ($i = 0; $i < 10; $i++) {
            Order::factory()->create([
                'user_id' => $users->random()->id,
                'client_id' => $clients->random()->id,
                'mesa_id' => $orderTypes[$i % 3] === 'dine_in' ? $mesas->random()->id : null,
                'metodo_pago_id' => $metodos->random()->id,
                'type' => $orderTypes[$i % 3],
                'status' => $orderStatuses[$i % 5],
                'payment_method' => $paymentMethods[$i % 4],
                'payment_status' => $paymentStatuses[$i % 3],
            ]);
        }
        $allOrders = Order::all();
        $this->command->info('✓ 10 pedidos creados');

        OrderItem::factory()->count(10)->create(fn () => [
            'order_id' => $allOrders->random()->id,
            'product_id' => $products->random()->id,
            'product_variant_id' => $variants->random()->id,
        ]);
        $this->command->info('✓ 10 items de pedido creados');

        AuditLog::factory()->count(10)->create(
            fn () => ['user_id' => $users->random()->id]
        );
        $this->command->info('✓ 10 auditorías creadas');
    }
}
