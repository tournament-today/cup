<?php namespace Syn\Cup\Models;

use Syn\Framework\Abstracts\Model;

class Round extends Model
{
	public $_validation = [
		'cup_id' => ['required', 'exists:cups,id'],

		'planned_at' => ['date:YY/mm/dd HH:mm', 'required'],
	];

	public function matches()
	{
		return $this->hasMany(__NAMESPACE__.'\Match');
	}

	public function teams()
	{
		return $this->hasManyThrough(__NAMESPACE__.'\Participant\Team', __NAMESPACE__.'\Match\Competitor');
	}

	/**
	 * Whether the round is finished
	 * @return bool
	 */
	public function getFinishedAttribute()
	{
		return $this -> matches() -> whereNull('approved_at') == 0;
	}
}