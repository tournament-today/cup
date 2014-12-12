<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MatchWinnerIdColumn extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('matches', function($table)
		{
			$table->bigInteger('winner_id')
				->nullable()
				->unsigned()
				->after('round_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('matches', function($table)
		{
			$table->dropColumn('winner_id');
		});
	}

}
