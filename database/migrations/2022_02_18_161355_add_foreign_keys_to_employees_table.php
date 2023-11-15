<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToEmployeesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('employees', function(Blueprint $table)
		{
			$table->foreign('company_id', 'employees_company_id')->references('id')->on('companies')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('department_id', 'employees_department_id')->references('id')->on('departments')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('designation_id', 'employees_designation_id')->references('id')->on('designations')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('office_shift_id', 'employees_office_shift_id')->references('id')->on('office_shifts')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('employees', function(Blueprint $table)
		{
			$table->dropForeign('employees_company_id');
			$table->dropForeign('employees_department_id');
			$table->dropForeign('employees_designation_id');
			$table->dropForeign('employees_office_shift_id');
			$table->dropForeign('employees_role_users_id');
		});
	}

}
