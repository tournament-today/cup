<?php namespace Syn\Cup\Models;

use App;
use Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use Illuminate\Support\Collection;
use Syn\Cup\Classes\RoundIterator;
use Syn\Framework\Abstracts\Model;
use Syn\Framework\Exceptions\MissingMethodException;
use Syn\Framework\Exceptions\UnexpectedResultException;

class Cup extends Model
{
	use SoftDeletingTrait;

	public $_validation = [
		'clan_id' => ['required', 'exists:clans,id'],
		'gamer_id' => ['required', 'exists:gamers,id'],
		'competition_type_id' => ['required', 'exists:competition_types,id'],
		'name' => ['required'],
		'description' => ['required'],
		'steam_game_id' => ['required'],
		'invite_only' => ['boolean'],
		'public' => ['boolean'],
		'disputable' => ['boolean'],
		'human_admin' => ['boolean'],
		'use_trustworthiness' => ['boolean'],
		'commercial' => ['boolean'],
		'entry_fee' => ['integer', 'disabled'],
		'award_honor' => ['boolean'],
		'award_prizes' => ['boolean'],
		'starts_at' => ['required', 'date'],
		'closes_at' => ['date'],
		'team_size' => ['integer', 'min:1', 'max:10'],
		'teams_max' => ['integer', 'min:2', 'max:128'],
		'teams_min' => ['integer', 'min:2', 'max:128'],
		'days' => ['integer', 'min:1', 'max: 30', 'disabled'],
		'play_weekdays' => ['boolean', 'required_without:play_weekends'],
		'play_weekends' => ['boolean', 'required_without:play_weekdays'],
		'daily_play_time_starts' => ['time'],
		'daily_play_time_ends' => ['time'],
	];

	public $_types = [
		'gamer_id' => 'Visitor.id',
		'name' => 'text',
		'clan_id' => 'select',
		'competition_type_id' => 'select',
		'description' => 'wysiwyg',
		'steam_game_id' => 'select2',
		'invite_only' => 'toggle',
		'human_admin' => 'toggle',
		'disputable' => 'toggle',
		'use_trustworthiness' => 'toggle',
		'public' => 'toggle',
		'commercial' => 'toggle',
		'entry_fee' => 'text',
		'award_honor' => 'toggle',
		'award_prizes' => 'toggle',
		'starts_at' => 'datetime',
		'closes_at' => 'datetime',
		'team_size' => 'slider',
		'teams_max' => 'slider',
		'teams_min' => 'slider',
		'days' => 'slider',
		'play_weekdays' => 'toggle',
		'play_weekends' => 'toggle',
		'daily_play_time_starts' => 'time',
		'daily_play_time_ends' => 'time',
	];

	public $_select_values = [
		'steam_game_id' => ['Syn\Steam\Models\SteamGame', 'selectable'],
		'competition_type_id' => ['Syn\Cup\Models\CompetitionType', 'all'],
		'clan_id' => ['Visitor', 'clans'],
	];

	protected $appends = [
		'upcoming',
	];

	public function getDates() { return ['starts_at', 'closes_at']; }

	/**
	 * Clan relation
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function clan()
	{
		return $this -> belongsTo('Syn\Clan\Models\Clan');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function steamGame()
	{
		return $this -> belongsTo('Syn\Steam\Models\SteamGame');
	}

	/**
	 * Cup teams
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function teams()
	{
		return $this -> hasMany(__NAMESPACE__.'\Participant\Team');
	}

	/**
	 * @param int $round
	 * @throws \Syn\Framework\Exceptions\UnexpectedResultException
	 * @return mixed
	 */
	public function teamsForRound($round = 1)
	{
		if($round == 1)
			return $this -> teams;
		// decrease the round, because we need the winners of the previous round
		$round--;
		// get all matches/winners of the previous round
		$round = $this->rounds()->find($round);
		if(!$round)
			throw new UnexpectedResultException("No teams");

		// winner Id's
		$winners = $round->matches->lists('winner_id');
		// list all teams who were the winner
		return !empty($winners) ? $this->teams()->whereIn($winners)->get() : [];
	}

	public function getIteratorAttribute()
	{
		return new RoundIterator($this->rounds);
	}

	public function byeTeam($round = 2)
	{
		throw new MissingMethodException('Not yet configured');
	}

	/**
	 * All team members in Cup
	 * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
	 */
	public function teamMembers()
	{
		return $this -> hasManyThrough(__NAMESPACE__.'\Participant\Team\Member', __NAMESPACE__.'\Participant\Team', 'cup_id', 'participant_team_id');
	}

	/**
	 * Rounds in Cup
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function rounds()
	{
		return $this -> hasMany(__NAMESPACE__.'\Round');
	}

	/**
	 * All matches in Cup
	 * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
	 */
	public function matches()
	{
		return $this -> hasManyThrough(__NAMESPACE__.'\Match', __NAMESPACE__.'\Round');
	}

	public function type()
	{
		return $this -> belongsTo(__NAMESPACE__.'\CompetitionType', 'competition_type_id');
	}

	/**
	 * Transfers db value to Carbon
	 * @param $value
	 * @return Carbon
	 */
	public function getStartsAtAttribute($value)
	{
		return new Carbon($value);
	}

	public function allowDelete()
	{
		return $this -> allowCreate() && $this -> started_at < Carbon::now();
	}
	public function allowCreate()
	{
		return Auth::check() && Auth::user()->admin;
	}
	public function allowEdit()
	{
		return $this -> allowCreate();
	}
	public function getUpcomingAttribute()
	{
		return $this -> starts_at -> isFuture();
	}

	/**
	 * Visitor can sign up on this cup
	 * @return bool
	 */
	public function getVisitorCanSignUpAttribute()
	{
		$visitor = App::make('Visitor');
		return
			// user must have e-mail address verified
			$visitor -> email_verified &&
			// cup is still open for sign ups, close at moment not reached
			(empty($this -> closes_at) ? $this->closes_at > Carbon::now() : $this->starts_at > Carbon::now())
			// there are still slots open (TODO remove from code with flexible slots)
			&& $this -> teams -> count() < (int) $this -> teams_max
			// user cannot participate if already participating
			&& !$this -> visitorParticipates;
	}

	/**
	 * Visitor is participating in this cup
	 * @return bool
	 */
	public function getVisitorParticipatesAttribute()
	{
		$visitor = App::make('Visitor');
		return $visitor -> exists && $this->teamMembers()->whereHas('gamer', function($q) use ($visitor)
		{
			$q -> where('id', $visitor->id) -> whereNotNull('accepted_at');
		})->count() > 0;
	}

	/**
	 * Visitor is invited for this cup
	 * @return bool
	 */
	public function getVisitorInvitedAttribute()
	{
		$visitor = App::make('Visitor');
		return $visitor -> exists && $this->teamMembers()->whereHas('gamer', function($q) use ($visitor)
		{
			$q -> where('id', $visitor->id) -> whereNull('accepted_at');
		})->count() > 0;
	}

	/**
	 * Visitor invites for this cup
	 * @return null
	 */
	public function getVisitorInvitesAttribute()
	{
		$visitor = App::make('Visitor');
		return $visitor -> exists ? $this->teamMembers()->whereHas('gamer', function($q) use ($visitor)
		{
			$q -> where('id', $visitor->id) -> whereNull('accepted_at');
		}) -> get() : null;
	}
}