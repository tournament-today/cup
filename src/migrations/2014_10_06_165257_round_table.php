<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RoundTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('rounds', function($table)
		{
			$table -> bigIncrements('id');
			$table -> bigInteger('cup_id') -> unsigned();
			$table -> timestamp('planned_at') -> nullable();
			$table -> timestamps();
			$table -> softDeletes();

			$table -> index('cup_id');

			$table -> foreign('cup_id') -> references('id') -> on('cups');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('rounds');
	}

}
