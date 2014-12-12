<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ParticipantTeamMemberTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('participant_team_members', function($table)
		{
			$table -> bigIncrements('id');
			$table -> bigInteger('participant_team_id') -> unsigned();
			$table -> bigInteger('gamer_id') -> unsigned();

			$table -> timestamp('invited_at');
			$table -> boolean('leader')->default(false);

			$table -> timestamps();
			$table -> softDeletes();

			$table -> index('gamer_id');
			$table -> index('participant_team_id');

			$table -> foreign('gamer_id') -> references('id') -> on('gamers');
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
		Schema::drop('participant_team_members');
	}

}
