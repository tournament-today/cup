<?php namespace Syn\Cup\Models\Participant;

use Illuminate\Database\Eloquent\SoftDeletingTrait;
use Syn\Framework\Abstracts\Model;

class Team extends Model
{
	use SoftDeletingTrait;
	protected $table = 'participant_teams';

	public $_validation = [
		'cup_id' => ['required', 'exists:cups,id'],
		'name' => ['required', 'between:5,30', 'team_name'],
		// coming out for clan x
		'clan_id' => ['exists:clans,id'],
		// who created it and owns the team
		'gamer_id' => ['required', 'exists:gamers,id'],
		'members' => ['required', 'min:1']
	];

	public $_types = [
		'name' => 'text',
		'clan_id' => 'select',
		'members' => 'auto-complete'
	];
	public $_select_values = [
		'clan_id' => ['Visitor', 'clans'],
		'members' => ['Gamer@auto-complete']
	];

	/**
	 * Members of this team
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function members()
	{
		return $this -> hasMany(__CLASS__.'\Member', 'participant_team_id');
	}

	/**
	 * Is only one player
	 * @return bool
	 */
	public function getAloneAttribute()
	{
		return $this -> members() -> count() == 1;
	}

	/**
	 * Cup
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function cup()
	{
		return $this -> belongsTo('Syn\Cup\Models\Cup');
	}

	/**
	 * Representing clan
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function clan()
	{
		return $this -> belongsTo('Syn\Clan\Models\Clan');
	}

	/**
	 * Whether current visitor is leader of the team
	 * @return bool
	 */
	public function getVisitorIsLeaderAttribute()
	{
		return in_array($this -> visitor -> id,$this -> members()->where('leader', true)->lists('gamer_id'));
	}

	/**
	 * Link to join team
	 * @return string
	 */
	public function getLinkJoinAttribute()
	{
		return route('Cup@joinTeam', ['id' => $this -> cup_id, 'name' => $this->cup->name, 'team' => $this->id, 'team_name'=>$this->name]);
	}

	/**
	 * Returns the first leader in the team
	 * @return mixed
	 */
	public function getLeaderAttribute()
	{
		return $this -> members() -> where('leader', true) -> first();
	}

	/**
	 * Shows logo or avatar
	 * @return null
	 */
	public function getLogoUriAttribute()
	{
		if($this->clan)
			return $this->clan->logoUri;
		elseif($this->leader)
			return $this->leader->gamer->avatar;

		return null;
	}
}