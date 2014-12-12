<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EliminationTypesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('competition_types', function($table)
		{
			$table -> increments('id');

			$table -> string('name');


			/**
			 * Elimination, group (future: ladder)
			 */

			$table -> integer('elimination');

			/**
			 * Elimination tournament settings
			 */
			// single, double or N elimination, after how many rounds a team is eliminated
			$table -> integer('elimination_after') -> nullable();

			/**
			 * Group tournaments
			 */
			$table -> integer('points_win') -> nullable();
			$table -> integer('points_draw') -> nullable();
			$table -> integer('points_loss') -> nullable();
			$table -> integer('points_no_show') -> nullable();
			$table -> integer('points_forfeit') -> nullable();

			/**
			 * General settings
			 */
			// when amount of teams is not of power 2, allow the additional team to have a bye
			$table -> integer('bye_enabled') -> nullable();
			$table -> boolean('selectable') -> default(true);
			$table -> boolean('admin_only') -> default(false);

			$table -> timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('competition_types');
	}

}
