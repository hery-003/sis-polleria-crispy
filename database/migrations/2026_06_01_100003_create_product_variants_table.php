<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->string('image')->nullable();
            $table->string('image_thumbnail')->nullable();
            $table->integer('stock')->nullable();
            $table->integer('stock_threshold')->default(5);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('stock');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
