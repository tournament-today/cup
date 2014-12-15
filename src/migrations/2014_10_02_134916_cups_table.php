<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CupsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cups', function($table)
		{
			$table -> bigIncrements('id');
			$table -> bigInteger('clan_id') -> unsigned();
			$table -> bigInteger('gamer_id') -> unsigned();
			$table -> string('name');
			$table -> text('description');
			$table -> bigInteger('steam_game_id') -> unsigned();
			$table -> boolean('invite_only');
			$table -> boolean('public');
			$table -> boolean('commercial');

			$table -> integer('entry_fee') -> nullable();

			$table -> boolean('award_honor');
			$table -> boolean('award_prizes');

			$table -> timestamp('starts_at') -> nullable();
			$table -> integer('team_size');
			$table -> integer('teams_max') -> nullable();
			$table -> integer('teams_min');
			$table -> integer('days') -> default(1);

			$table -> boolean('play_weekdays');
			$table -> boolean('play_weekends');

			$table -> string('daily_play_time_starts') -> nullable();
			$table -> string('daily_play_time_ends') -> nullable();

			$table -> timestamps();
			$table -> softDeletes();

			$table -> index('clan_id');
			$table -> index('gamer_id');
			$table -> index('steam_game_id');

			$table -> foreign('clan_id') -> references('id') -> on('clans');
			$table -> foreign('gamer_id') -> references('id') -> on('gamers');
			$table -> foreign('steam_game_id') -> references('id') -> on('steam_games');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('cups');
	}

}
