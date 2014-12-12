<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MatchCompetitorsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('match_competitors', function($table)
		{
			$table -> bigIncrements('id');
			$table -> bigInteger('match_id') -> unsigned();
			$table -> bigInteger('participant_team_id') -> unsigned();
			$table -> timestamps();
			$table -> softDeletes();

			$table -> index('match_id');
			$table -> index('participant_team_id');

			$table -> foreign('match_id') -> references('id') -> on('matches');
			$table -> foreign('participant_team_id') -> references('id') -> on('participant_teams');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('match_competitors');
	}

}
