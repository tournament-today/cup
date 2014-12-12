<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MatchTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('matches', function($table)
		{
			$table -> bigIncrements('id');
			$table -> bigInteger('round_id') -> unsigned();

			$table -> timestamp('planned_at') -> nullable();
			$table -> timestamp('started_at') -> nullable();
			$table -> timestamp('finished_at') -> nullable();
			$table -> timestamp('approved_at') -> nullable();
			$table -> timestamp('disputed_at') -> nullable();
			$table -> integer('approved_by') -> nullable();

			$table -> timestamps();
			$table -> softDeletes();

			$table -> index('round_id');

			$table -> foreign('round_id') -> references('id') -> on('rounds');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('matches');
	}

}
