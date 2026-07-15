<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cash_register_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('mesa_id')->nullable()->constrained('mesas')->nullOnDelete();
            $table->foreignId('metodo_pago_id')->nullable()->constrained('metodos_pago')->nullOnDelete();
            $table->string('order_number')->unique();
            $table->decimal('total_amount', 10, 2);
            $table->decimal('received_amount', 10, 2)->nullable();
            $table->decimal('change', 10, 2)->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('cancellation_reason')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_status', 20)->default('pending');
            $table->string('type', 20)->default('dine_in');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['payment_status', 'created_at']);
            $table->index(['payment_method', 'payment_status']);
            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
