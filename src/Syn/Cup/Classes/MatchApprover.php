<?php namespace Syn\Cup\Classes;

use Illuminate\Support\Collection;
use Syn\Cup\Models\Cup;
use Syn\Cup\Models\Match;

class MatchApprover
{
	protected $match;
	protected $cup;
	protected $approved = false;

	/**
	 * @param Match $match
	 * @param Cup   $cup
	 */
	public function __construct(Match $match, Cup $cup)
	{
		$this -> match = $match;
		$this -> cup = $cup;

		$this->generateAttributes();
	}

	/**
	 * re-generates attributes
	 */
	protected function generateAttributes()
	{
		// approved already
		if($this->match->approved_at)
		{
			$this->approved = true;
			return;
		}

		$scoring = [];

		// first test for match results
		$results = $this -> match -> matchResults;
		if($results)
		{
			foreach($results as $result)
			{
				// FIXME
				/*$scoring[$result->competitor_id][$result->score] = new Collection();
				$scoring[$result->competitor_id][$result->score] -> put($result -> gamer_id, $result);*/
			}
		}
	}
}