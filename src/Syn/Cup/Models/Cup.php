<?php namespace Syn\Cup\Models;

use App;
use Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use Syn\Cup\Classes\RoundIterator;
use Syn\Framework\Abstracts\Model;
use Syn\Framework\Exceptions\MissingMethodException;
use Syn\Framework\Exceptions\UnexpectedResultException;

class Cup extends Model
{
	use SoftDeletingTrait;

	/**
	 * Validation definition
	 * @var array
	 */
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
		'teams_min' => ['integer', 'min:2', 'max:128'],
		'teams_max' => ['integer', 'min:2'],
		'days' => ['integer', 'min:1', 'max: 30', 'disabled'],
		'play_weekdays' => ['boolean', 'required_without:play_weekends'],
		'play_weekends' => ['boolean', 'required_without:play_weekdays'],
		'daily_play_time_starts' => ['time'],
		'daily_play_time_ends' => ['time'],
	];

	/**
	 * Type definition
	 * @var array
	 */
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
		'teams_min' => 'slider',
		'teams_max' => 'text',
		'days' => 'slider',
		'play_weekdays' => 'toggle',
		'play_weekends' => 'toggle',
		'daily_play_time_starts' => 'time',
		'daily_play_time_ends' => 'time',
	];

	/**
	 * Loads the following methods for select input values
	 * @var array
	 */
	public $_select_values = [
		'steam_game_id' => ['Syn\Steam\Models\SteamGame', 'selectable'],
		'competition_type_id' => ['Syn\Cup\Models\CompetitionType', 'all'],
		'clan_id' => ['Visitor', 'clans'],
	];

	/**
	 * Appended attributes for model
	 * @var array
	 */
	protected $appends = [
		'upcoming',
	];

	public function getDates() { return ['starts_at', 'closes_at']; }

	/**
	 * Vms
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function vms()
	{
		return $this -> hasMany('Syn\Vm\Models\Vm');
	}
	public function getUninstantiatedVmsAttribute()
	{
		return $this->vms()->whereNull('deploying_at')->get();
	}
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

	/**
	 * Loads the Round Iterator instance
	 * @return RoundIterator
	 */
	public function getIteratorAttribute()
	{
		return new RoundIterator($this->rounds);
	}

	/**
	 * @param int $round
	 * @throws \Syn\Framework\Exceptions\MissingMethodException
	 */
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
	 * Calculates the average duration to instantiate all necessary machines based on Vm Provider
	 * @return int
	 */
	public function getVmCreateDurationAttribute()
	{
		$vm = App::make('vm.instance');
		return $vm->createDuration() * ($this -> teams -> count() / 2);
	}

	/**
	 * Calculate the average duration to destroy all instantiated machines
	 * @return int
	 */
	public function getVmDestroyDurationAttribute()
	{
		$vm = App::make('vm.instance');
		return $vm->destroyDuration() * ($this -> teams -> count() / 2);
	}

	/**
	 * When to start instantiation
	 * @return mixed
	 */
	public function getVmInstantiationAtAttribute()
	{
		return $this -> starts_at -> subSecond($this -> vmCreateDuration);
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

	/**
	 * Competition type
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function type()
	{
		return $this -> belongsTo(__NAMESPACE__.'\CompetitionType', 'competition_type_id');
	}

	/**
	 * closes_at ; if null will use calculated closure time
	 * @param $value
	 * @return Carbon|mixed|null
	 */
	public function getClosesAtAttribute($value)
	{
		return empty($value) || $value == '0000-00-00 00:00:00'
			? ($this -> starts_at ? $this -> vmInstantiationAt : null)
			: new Carbon($value);
	}

	/**
	 * Whether visitor is allowed to Delete
	 * @return bool
	 */
	public function allowDelete()
	{
		return $this -> allowCreate() && $this -> started_at < Carbon::now();
	}


	/**
	 * Whether visitor is allowed to Create
	 * @return bool
	 */
	public function allowCreate()
	{
		return Auth::check() && Auth::user()->admin;
	}

	/**
	 * Whether visitor is allowed to Edit
	 * @return bool
	 */
	public function allowEdit()
	{
		return $this->teams->count() == 0 && $this -> allowCreate();
	}

	/**
	 * Whether the cup is in the future
	 * @return mixed
	 */
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
			&& ($this -> teams_max ? $this -> teams -> count() < (int) $this -> teams_max : true)
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