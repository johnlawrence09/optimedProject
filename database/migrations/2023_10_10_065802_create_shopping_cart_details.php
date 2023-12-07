<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shopping_cart_details', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('cart_id');
            $table->integer('product_id');
            $table->string('name', 200);
			$table->float('price', 10, 0);
            $table->integer('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopping_cart_details');
    }
};
