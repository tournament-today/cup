<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CupsAddHumanColumns extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('cups', function($table)
		{
			$table -> boolean('use_trustworthiness') -> default(true) -> after('invite_only');
			$table -> boolean('human_admin') -> default(false) -> after('invite_only');
			$table -> boolean('disputable') -> default(false) -> after('invite_only');
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
			$table -> dropColumn('use_trustworthiness');
			$table -> dropColumn('human_admin');
			$table -> dropColumn('disputable');
		});
	}

}
