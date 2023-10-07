<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaleReceiveDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_receive_details', function (Blueprint $table) {
            $table->id(); // Auto-incremental primary key column
            $table->double('cost')->nullable();
            $table->unsignedInteger('purchase_unit_id')->nullable();
            $table->double('TaxNet')->nullable()->default(0);
            $table->string('tax_method', 192)->nullable()->default('1');
            $table->double('discount')->nullable()->default(0);
            $table->string('discount_method', 192)->nullable()->default('1');
            $table->unsignedInteger('purchase_received')->nullable();
            $table->unsignedInteger('product_id')->nullable();
            $table->unsignedInteger('product_variant_id')->nullable();
            $table->text('imei_number')->nullable();
            $table->double('total')->nullable();
            $table->double('quantity')->nullable();
            $table->date('expiration_date')->nullable();
            $table->double('lot_number')->nullable();
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
        Schema::dropIfExists('sale_receive_details');
    }
}
