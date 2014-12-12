<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CupCompetitionTypeId extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('cups', function($table)
		{
			$table -> integer('competition_type_id') -> unsigned() -> after('gamer_id');

			$table -> index('competition_type_id');
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
			$table -> dropColumn('competition_type_id');
		});
	}

}
