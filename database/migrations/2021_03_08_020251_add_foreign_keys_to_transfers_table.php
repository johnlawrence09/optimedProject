<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToTransfersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('transfers', function(Blueprint $table)
		{
			$table->foreign('from_warehouse_id', 'from_warehouse_id')->references('id')->on('warehouses')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('to_warehouse_id', 'to_warehouse_id')->references('id')->on('warehouses')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('user_id', 'user_id_transfers')->references('id')->on('users')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('transfers', function(Blueprint $table)
		{
			$table->dropForeign('from_warehouse_id');
			$table->dropForeign('to_warehouse_id');
			$table->dropForeign('user_id_transfers');
		});
	}

}
