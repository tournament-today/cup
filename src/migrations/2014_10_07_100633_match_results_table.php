<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MatchResultsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('match_results', function($table)
		{
			$table -> bigIncrements('id');
			$table -> bigInteger('match_id') -> unsigned();
			$table -> bigInteger('competitor_id') -> unsigned();
			$table -> bigInteger('gamer_id') -> unsigned();
			$table -> integer('trustworthiness')->nullable();
			$table -> integer('score');
			$table -> string('score_image')->nullable();

			$table -> timestamps();
			$table -> softDeletes();

			$table -> index('match_id');
			$table -> index('gamer_id');
			$table -> index('competitor_id');

			$table -> foreign('match_id') -> references('id') -> on('matches');
			$table -> foreign('gamer_id') -> references('id') -> on('gamers');
			$table -> foreign('competitor_id') -> references('id') -> on('match_competitors');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('match_results');
	}

}
