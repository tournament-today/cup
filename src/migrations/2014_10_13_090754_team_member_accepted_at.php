<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TeamMemberAcceptedAt extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('participant_team_members', function($table)
		{
			$table->timestamp('accepted_at') -> nullable() -> after('invited_at');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('participant_team_members', function($table)
		{
			$table->dropColumn('accepted_at');
		});
	}

}
