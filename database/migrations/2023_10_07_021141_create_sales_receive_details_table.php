<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesReceiveDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_receive_details', function (Blueprint $table) {
            $table->engine = 'InnoDB';
			$table->integer('id', true);
			$table->float('cost', 10, 0);
			$table->float('TaxNet', 10, 0)->nullable()->default(0);
			$table->string('tax_method', 192)->nullable()->default('1');
			$table->float('discount', 10, 0)->nullable()->default(0);
			$table->string('discount_method', 192)->nullable()->default('1');
			$table->integer('sales_receive_id')->index('sales_receive_id');
			$table->integer('product_id')->index('product_id');
			$table->integer('product_variant_id')->nullable()->index('sales_product_variant_id');
			$table->float('total', 10, 0);
			$table->float('quantity', 10, 0);
			$table->timestamps(6);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_receive_details');
    }
}
