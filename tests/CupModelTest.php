<?php namespace Syn\Cup;

use Carbon\Carbon;
use Syn\Cup\Models\Cup;
use TestCase;

class CupModelTest extends TestCase
{

	protected $default_values = [
		'clan_id' => 1,
		'gamer_id' => 1,
		'competition_type_id' => 1,
		'name' => 'unit tested tournament',
		'description' => 'this tournament is auto created through a unit test and will be removed within moments',
		'steam_game_id' => 5,
		'invite_only' => true,
		'public' => false,
		'disputable' => false,
		'human_admin' => false,
		'use_trustworthiness' => false,
		'commercial' => false,
		'entry_fee' => null,
		'award_honor' => false,
		'award_prizes' => false,
		'starts_at' => null, // must be set manually
		'closes_at' => null,
		'team_size' => 1,
		'teams_min' => 2,
		'teams_max' => 2,
		'days' => 0,
		'play_weekdays' => true,
		'play_weekends' => true,
		'daily_play_time_starts' => null,
		'daily_play_time_ends' => null,
	];


	public function testCreation()
	{
		$cup = new Cup;
		$cup -> unguard();
		$cup -> fill($this->default_values);

		$cup -> starts_at = Carbon::now() -> addYear();

		$this->assertTrue($cup->save());

		$this->assertEquals($cup->clan_id, array_get($this->default_values, 'clan_id'));
		$this->assertEquals($cup->competition_type_id, array_get($this->default_values, 'competition_type_id'));
		$this->assertEquals($cup->name, array_get($this->default_values, 'name'));
		$this->assertEquals($cup->description, array_get($this->default_values, 'description'));
		$this->assertEquals($cup->steam_game_id, array_get($this->default_values, 'steam_game_id'));
		$this->assertEquals($cup->invite_only, array_get($this->default_values, 'invite_only'));
		$this->assertEquals($cup->public, array_get($this->default_values, 'public'));
		$this->assertEquals($cup->disputable, array_get($this->default_values, 'disputable'));
		$this->assertEquals($cup->human_admin, array_get($this->default_values, 'human_admin'));
		$this->assertEquals($cup->use_trustworthiness, array_get($this->default_values, 'use_trustworthiness'));
		$this->assertEquals($cup->commercial, array_get($this->default_values, 'commercial'));
		$this->assertEquals($cup->entry_fee, array_get($this->default_values, 'entry_fee'));
		$this->assertEquals($cup->award_honor, array_get($this->default_values, 'award_honor'));
		$this->assertEquals($cup->award_prizes, array_get($this->default_values, 'award_prizes'));
		$this->assertEquals($cup->team_size, array_get($this->default_values, 'team_size'));
		$this->assertEquals($cup->teams_min, array_get($this->default_values, 'teams_min'));
		$this->assertEquals($cup->teams_max, array_get($this->default_values, 'teams_max'));
		$this->assertEquals($cup->days, array_get($this->default_values, 'days'));
		$this->assertEquals($cup->play_weekdays, array_get($this->default_values, 'play_weekdays'));
		$this->assertEquals($cup->play_weekends, array_get($this->default_values, 'play_weekends'));
		$this->assertEquals($cup->daily_play_time_starts, array_get($this->default_values, 'daily_play_time_starts'));
		$this->assertEquals($cup->daily_play_time_ends, array_get($this->default_values, 'daily_play_time_ends'));

		return $cup;
	}

	/**
	 * @depends testCreation
	 */
	public function testSoftDeletion(Cup $cup)
	{
		$this -> assertTrue($cup -> delete());

		$this -> assertTrue($cup -> restore());

		return $cup;
	}


	/**
	 * @depends testSoftDeletion
	 */
	public function testAccess(Cup $cup)
	{

		$this->assertTrue($cup->allowView());

		$this->assertFalse($cup->allowEdit());

		$this->assertFalse($cup->allowDelete());

		return $cup;
	}

	/**
	 * @depends testAccess
	 */
	public function testHardDeletion(Cup $cup)
	{
		$this -> assertNull($cup -> forceDelete(), 'If forcedelete trait is used, null is returned otherwise Eloquent model will return true or false as fallback');
		$this -> assertNull(Cup::find($cup->id));
	}
}