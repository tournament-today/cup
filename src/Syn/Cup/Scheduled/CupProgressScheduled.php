<?php namespace Syn\Cup\Scheduled;

use App;
use Carbon\Carbon;
use Indatus\Dispatcher\Scheduling\Schedulable;
use Indatus\Dispatcher\Scheduling\ScheduledCommand;
use Syn\Cup\Classes\MatchApprover;
use Syn\Cup\Classes\RoundGenerator;
use Syn\Cup\Models\Cup;
use Syn\Cup\Models\Match;

class CupProgressScheduled extends ScheduledCommand
{
	protected $name = 'cup:progress';
	protected $description = 'Moves cups to the next round, closes matches etc.';


	public function fire()
	{
		$repo = App::make('Syn\Cup\Interfaces\CupRepositoryInterface');
		// loads all cups that start within the hour
		$cups = $repo->findNeedAttention();

		foreach($cups as $i => $cup)
		{
			$this -> info("#{$i}: {$cup->name}");

			/**
			 * Loop through all started, open matches
			 * - approve them if possible
			 */
			foreach($cup->matches()
				->whereNotNull('started_at')
				->whereNull('approved_at')
				->get() as $match
			)
			{
				$this->info("#{$i}: started match {$match->id} between team Id's: ". implode(", ", $match->competitors->lists('participant_team_id')));
				// if scores by all players are entered, do something
				$approved = $this->attemptMatchApproval($match, $cup);
				// if longer than x minutes after finishing match, do something

				// if match is "lost"; takes longer than expected max match time, do something

			}
			/**
			 * closes_at reached
			 * - no new teams can sign up
			 * - decide whether enough teams signed up
			 * -
			 */
			// not enough teams entered, erase cup, notify?
			if($cup->closes_at <= Carbon::now() && $cup -> teams -> count() < $cup -> teams_min)
			{
				$cup -> delete();
				continue;
			}
			/**
			 * starts_at reached
			 * - teams_min not reached - delete and inform participants
			 * - generate brackets round 1
			 */
			if($cup->starts_at <= Carbon::now() && ($cup->rounds->count() == 0 || $cup->matches->count() == 0))
			{
				$this -> info("#{$i}: generating round and matches");
				$generator = new RoundGenerator($cup);
//				foreach($generator -> cup -> rounds as $round)
//					$this -> info("{$round->planned_at}");
			}
			/**
			 * round finished
			 * - generate next round
			 * - close cup, select winner; set finished_at
			 */
		}
	}

	protected function attemptMatchApproval(Match $match, Cup $cup)
	{
		$approver = new MatchApprover($match, $cup);
	}

	/**
	 * @param Schedulable $scheduler
	 * @return Schedulable|\Indatus\Dispatcher\Scheduling\Schedulable[]
	 */
	public function schedule(Schedulable $scheduler)
	{
		return $scheduler
			-> everyMinutes(1);
	}

	/**
	 * @return array|string
	 */
	public function environment()
	{
		return ['never'];
//		return ['production'];
	}

	/**
	 * @return bool
	 */
	public function runInMaintenanceMode()
	{
		return false;
	}
}