<?php namespace Syn\Cup\Classes;

use Config;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Collection;
use Syn\Cup\Models\Cup;
use Syn\Cup\Models\Match;
use Syn\Cup\Models\Match\Competitor;
use Syn\Cup\Models\Round;
use Syn\Framework\Exceptions\MissingConfigurationException;
use Syn\Framework\Exceptions\UnexpectedResultException;

class RoundGenerator
{

	protected $cup;
	protected $iterator;
	protected $round;

	/**
	 * @param Cup  $cup
	 * @param null $iterator
	 * @throws \Syn\Framework\Exceptions\MissingConfigurationException
	 */
	public function __construct(Cup $cup, $iterator = null)
	{
		$this -> cup = $cup;
		$this -> iterator = !is_null($iterator) ? $iterator : new RoundIterator();
		// seeks current round
		if($this->cup->rounds->count() > 0)
			$this->iterator->fill($this->cup->rounds);
		else
			$this->iterator->fill($this->generateRounds());

/*		$round = false;

		DB::beginTransaction();

		// no rounds yet
		if($this->iterator->key() == 0)
		{
			// create a round
			$round = $this->planNextRound();
			// fill the iterator
			$this->iterator->fill([$round]);
			// move current upwards
			$this->iterator->next();
		}

		// no round set yet; get current round from iterator
		if(!$round)
			$round = $this -> iterator -> current();

		// we should have a round
		if(!$round)
			throw new MissingConfigurationException("Cannot generate cup without a round");

		$this -> round = $round;
		$this->planNextMatches();

		DB::commit();*/
	}

	/**
	 * @return null|RoundIterator
	 */
	public function getIterator()
	{
		return $this->iterator;
	}

	/**
	 * Calculates and creates the required number of rounds / brackets
	 * @info take into account
	 *       - odd number of teams require bye if available (what to do with the odd team?)
	 *       - odd number of matches in rounds use bye
	 */
	public function generateRounds()
	{
		// count the required number of rounds in the brackets
		// 6; 4, 2+2, 2				4 and 2 byes
		// 9; 6, 3+1, 2+2, 2		6 and 3 byes
		// 11; 8, 4+2, 3+1, 2		8 and 3 byes
		// 8; 4, 2					4 and 0 byes
		// 7; 6, 3+1, 2				6 and 1 bye
		// 5: 4, 2, 1+1				4 and 1 bye
		// 14: 12, 6+2, 4, 2		12 and 2 byes
		// 33: 32, 16, 8, 4, 2, 1+1	32 and 1 bye

		// create transaction, preventing issues when mallfunctioning
		DB::beginTransaction();


		// the teams of this cup
		$teams = $this -> cup -> teams;
		// number of matches per round
		$matchesPerRound = ceil($teams->count()/2);

		$finalsReached = false;
		$roundNo = 1;

		while($finalsReached == false)
		{
			// instantiate new round
			$round = new Round;

			// round no
			$round -> round_no = $roundNo;
			// round planning
			if(!isset($lastPlanned))
				$round -> planned_at = $this->calculateNextPossibleTime(isset($lastPlanned) ? $lastPlanned : $this -> cup -> starts_at);

			// save round to cup
			$this->cup->rounds()->save($round);

			$loop = $matchesPerRound;

			for($i = 1; $i <= $loop; $i++)
			{
				// create match instance
				$match = new Match;
				// plan them for the round planned date
				//$match -> planned_at = $round -> planned_at;
				// save and link to round
				$round->matches()->save($match);

				// only create match label opponents for first round
				if($teams->count() > 0)
				{
					// pick 2 random contestants
					// todo allow more than 2 teams
					// todo other way of picking random teams
					$opponents = new Collection($teams->random(2));
					// remove these competitors from teams
					$teams = $teams -> except($opponents->lists('id'));

					// add each opponent to match
					foreach($opponents as $opponent)
					{
						// create competitor for match
						$competitor = new Competitor;
						// link to cup team
						$competitor->participant_team_id = $opponent->id;
						// save to match
						$match->competitors()->save($competitor);
					}
					// bye; auto-win
					if($match->competitors->count() == 1)
					{
						$match->winner_id = $competitor->id;
						$match->save();
					}
				}
			}
			// force stopping loop if we have finished generating all matches
			if($matchesPerRound == 1)
				$finalsReached = true;

			// reduces number of required matches of the next round
			$matchesPerRound = ceil($matchesPerRound/2);

			// when planning also adds 10 minutes, let's assume 90 minutes per round
			// todo define delay based on game
			if($round->planned_at)
				$lastPlanned = $round->planned_at->addMinutes(90);

			// increment round number with 1
			$roundNo++;

		}

		// support losing brackets, depending on type->elimination_after
		// seed starts in round two, for all teams that failed in the first round
		// todo FIX
		if($this->cup->type->elimination_after > 0)
		{

			for($i = 1; $i <= $this->cup->type->elimination_after; $i++)
			{

			}
		}

		// now commit everything to database
		DB::commit();

		return $this -> cup -> rounds;
	}

	/**
	 * Plans next matches
	 */
	public function planNextMatches()
	{
		// get all participants
		if($this->iterator->key() == 1)
			$teams = $this->cup->teams;
		else
			$teams = $this->cup->teamsForRound($this->iterator->key());

		while($teams->count() >= 2)
		{
			// get two random opponents
			// todo: maybe bring in skill to match
			$opponents = new Collection($teams->random(2));

			$match = new Match();
			$match->planned_at = $this -> round -> planned_at;
			$this->round->matches()->save($match);

			foreach($opponents as $opponent)
			{
				$competitor = new Competitor;
				$competitor->participant_team_id = $opponent->id;
				$match->competitors()->save($competitor);
			}

			$teams = $teams -> except($opponents->lists('id'));
		}

		// move this team to the next round.. How? Play against itself and set to done?
		// todo disabled for the time being
		if(false && $teams->count() == 1 && $this -> cup -> type -> bye_enabled)
		{
			$opponent = $teams->first();
			$match = new Match();
			$match->planned_at = $this -> round -> planned_at;
			$this->round->matches()->save($match);

			$competitor = new Competitor;
			$competitor->participant_team_id = $opponent->id;
			$match->competitors()->save($competitor);

//			$competitor = new Competitor;
//			$competitor->participant_team_id = $opponent->id;
//			$match->competitors()->save($competitor);
		}
	}
	/**
	 * Sets the next round and saves it.
	 */
	protected function planNextRound()
	{
		throw new \Exception('Obsolete');
		$round = new Round();

		$round -> planned_at = $this->calculateNextPossibleTime();

		$this -> cup -> rounds() -> save($round);

		return $round;
	}
	/**
	 * @info take into account week & weekend days
	 * @info take into account opening and closing hours
	 * @info take into account the length of a match with closing hours?
	 *
	 */
	protected function calculateNextPossibleTime($from = null)
	{
		// always allow for 10 minutes preparation
		if($from)
			$next 	= $from -> addMinutes(10);
		else
			$next	= Carbon::now() -> addMinutes(10);

		// move after cup start; should not be possible
		if($this -> cup -> starts_at > $next)
			$next = $this->cup->starts_at;


		// identify the day to start on
		$next = $this -> findNextSuitableDay($next);

		// identify first playing time
		$next = $this -> findNextSuitableTime($next);
		
		return $next;
		/*
		// move after daily play time starts
		if($this->cup->daily_play_time_starts)
		{
			list($start_after_hour,$start_after_minute) = explode(":", $this->cup->daily_play_time_starts);

			// forces to hour
			if($next < $start_after_hour)
			{
				$next -> hour = $start_after_hour;
				// forces to minute
				if($next -> minute < $start_after_minute)
					$next -> minute = $start_after_minute;
			}
		}
		if($this->cup->daily_play_time_starts)
		{
			list($stop_after_hour,$stop_after_minute) = explode(":", $this->cup->daily_play_time_starts);

			$test = $next -> copy();
			$test -> hour = $stop_after_hour;
			$test -> minute = $stop_after_minute;
			// move to next day
			if($next -> isSameDay($test) && $next >= $test)
			{
				$next -> addDay();
				if(isset($start_after_hour))
				{
					$next -> hour = $start_after_hour;
					$next -> minute = isset($start_after_minute) ? $start_after_minute : 0;
				}
				else
				{
					$next -> hour = Config::get('cup::scheduling.start-hour');
					$next -> minute = Config::get('cup::scheduling.start-minute');
				}
			}
		}

		// prevent reset of hour/minute/second in next possible steps
		$hour = $next -> copy();

		// force outside weekend
		if($next->isWeekend() && !$this->cup->play_weekends)
		{
			// if sunday move one day in advance, otherwise move two days
			$next -> addWeekdays($next->dayOfWeek == 0 ? 1 : 2);
		}
		// force into weekend
		if($next->isWeekDay() && !$this->cup->play_weekdays)
		{
			// 1 mon - 5 fri; adds number of days depending on week day
			$next -> addWeekdays(5 - $next->dayOfWeek);
		}

		// resets to previously "saved" hour
		$next -> setTime($hour -> hour, $hour -> minute);

		return $next;
		*/
	}

	protected function findNextSuitableDay(Carbon $date)
	{
		// we do not play in weekends
		if($date->isWeekend() && !$this -> cup -> play_weekends)
		{
			// if sunday move one day in advance, otherwise move two days
			$date -> addWeekdays($date->dayOfWeek == 0 ? 1 : 2);
		}
		// we do not play during the week
		elseif($date->isWeekday() && !$this -> cup -> play_weekdays)
		{
			// 1 mon - 5 fri; adds number of days depending on week day
			$date -> addWeekdays(5 - $date->dayOfWeek);
		}
		return $date;
	}

	protected function findNextSuitableTime(Carbon $date)
	{
		// first playable time:
		if($this -> cup -> daily_play_time_starts)
			list($hour, $minute) = explode(':', $this -> cup -> daily_play_time_starts);
		else
			if($date -> isWeekday())
				list($hour, $minute) = Config::get('cup::scheduling.start.weekday');
			elseif($date -> isWeekend())
				list($hour, $minute) = Config::get('cup::scheduling.start.weekend');

		// create lower limit
		$min = $date -> copy();
		$min -> hour = $hour;
		$min -> minute = $minute;
		$min -> second = 0;
		// last playable time:
		if($this -> cup -> daily_play_time_ends)
			list($hour, $minute) = explode(':', $this -> cup -> daily_play_time_ends);
		else
			if($date -> isWeekday())
				list($hour, $minute) = Config::get('cup::scheduling.end.weekday');
			elseif($date -> isWeekend())
				list($hour, $minute) = Config::get('cup::scheduling.end.weekend');

		// create upper limit
		$max = $date -> copy();
		$max -> hour = $hour;
		$max -> minute = $minute;
		$max -> second = 0;

		// planned time is before the starting time; move forwards
		if($date < $min)
			$date = $min;
		// planned time if after latest match time or is now planned in the past
		elseif($date > $max || $date -> isPast())
		{
			$date -> addDay();
			$date -> hour = 0;
			$date -> minute = 0;
			$date = $this -> findNextSuitableDay($date);
			return $this -> findNextSuitableTime($date);
		}

		return $date;
	}
}