<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToDesignationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('designations', function(Blueprint $table)
		{
			$table->foreign('company_id', 'designation_company_id')->references('id')->on('companies')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('department_id', 'designation_departement_id')->references('id')->on('departments')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('designations', function(Blueprint $table)
		{
			$table->dropForeign('designation_company_id');
			$table->dropForeign('designation_departement_id');
		});
	}

}
