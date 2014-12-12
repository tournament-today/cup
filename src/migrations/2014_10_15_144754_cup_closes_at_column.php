<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CupClosesAtColumn extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('cups', function($table)
		{
			$table -> timestamp('closes_at')->nullable()->after('starts_at');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('cups', function($table)
		{
			$table -> dropColumn('closes_at');
		});
	}

}
