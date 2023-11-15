<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToPurchaseReturnDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('purchase_return_details', function(Blueprint $table)
		{
			$table->foreign('product_id', 'product_id_details_purchase_return')->references('id')->on('products')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('purchase_return_id', 'purchase_return_id_return')->references('id')->on('purchase_returns')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('product_variant_id', 'purchase_return_product_variant_id')->references('id')->on('product_variants')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('purchase_return_details', function(Blueprint $table)
		{
			$table->dropForeign('product_id_details_purchase_return');
			$table->dropForeign('purchase_return_id_return');
			$table->dropForeign('purchase_return_product_variant_id');
		});
	}

}
