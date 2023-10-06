<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaleReceivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_receives', function (Blueprint $table) {
            $table->id(); // Auto-incremental primary key column
            $table->unsignedBigInteger('user_id'); // Foreign key to users table
            $table->string('Ref', 192)->nullable();
            $table->date('date');
            $table->unsignedBigInteger('purchase_id'); // Foreign key to purchases table
            $table->unsignedBigInteger('provider_id'); // Foreign key to providers table
            $table->unsignedBigInteger('warehouse_id'); // Foreign key to warehouses table
            $table->double('tax_rate')->nullable()->default(0);
            $table->double('TaxNet')->nullable()->default(0);
            $table->double('discount')->nullable()->default(0);
            $table->double('shipping')->nullable()->default(0);
            $table->double('GrandTotal')->nullable()->default(0);
            $table->double('paid_amount')->default(0);
            $table->string('statut', 191);
            $table->string('payment_statut', 192);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sale_receives');
    }
}
