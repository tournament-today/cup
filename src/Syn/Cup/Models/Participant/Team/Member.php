<?php namespace Syn\Cup\Models\Participant\Team;

use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use Syn\Cup\Models\Participant\Team;
use Syn\Framework\Abstracts\Model;

class Member extends Model
{
	use SoftDeletingTrait;

	protected $table = 'participant_team_members';
	/**
	 * Member is part of this team
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function team()
	{
		return $this -> belongsTo(__NAMESPACE__, 'participant_team_id');
	}

	/**
	 * The team member is actually this gamer
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function gamer()
	{
		return $this -> belongsTo('Syn\Gamer\Models\Gamer');
	}

	public function getCupAttribute()
	{
		return $this -> team ? $this -> team -> cup : null;
	}

	/**
	 * @param $team_id
	 * @param $gamer_id
	 * @return bool
	 * @TODO disallow this some period before Cup start?
	 * @TODO no check whether already registered for one team, will overrule that
	 */
	public static function registerForTeam($team_id, $gamer_id)
	{
		$team = Team::find($team_id);

		$members = Member::query()
			-> whereHas('team', function($q) use ($team)
			{
				$q -> where('cup_id', $team->cup_id);
			})
			-> where('gamer_id', $gamer_id)
			-> whereNull('deleted_at')
			-> get();

		if(!count($members))
			return false;

		DB::beginTransaction();
		foreach($members as $member)
		{
			if($member->participant_team_id == $team_id)
			{
				$member -> accepted_at = Carbon::now();
				$member -> save();
			}
			else
				$member -> delete();
		}
		DB::commit();

		return true;
	}
}