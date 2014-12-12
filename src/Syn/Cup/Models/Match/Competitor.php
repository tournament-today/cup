<?php namespace Syn\Cup\Models\Match;

use Syn\Framework\Abstracts\Model;

/**
 * Class MatchTeam
 * @info team assigned to a match
 *
 * @package Syn\Cup\Models\Match
 */
class Competitor extends Model
{
	protected $table = "match_competitors";
	/**
	 * Team
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function team()
	{
		return $this -> belongsTo('Syn\Cup\Models\Participant\Team', 'participant_team_id');
	}

	/**
	 * Match
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function match()
	{
		return $this -> belongsTo(__NAMESPACE__);
	}
}