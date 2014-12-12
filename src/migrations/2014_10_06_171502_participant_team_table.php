<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ParticipantTeamTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('participant_teams', function($table)
		{
			$table -> bigIncrements('id');
			$table -> bigInteger('cup_id') -> unsigned();
			$table -> string('name');
			$table -> bigInteger('clan_id') -> nullable() -> unsigned();
			$table -> bigInteger('gamer_id') -> unsigned();

			$table -> timestamps();
			$table -> softDeletes();

			$table -> index('cup_id');
			$table -> index('clan_id');
			$table -> index('gamer_id');

			$table -> foreign('gamer_id') -> references('id') -> on('gamers');
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
		Schema::drop('participant_teams');
	}

}
