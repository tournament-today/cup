<?php namespace Syn\Cup\Models;

use Syn\Framework\Abstracts\Model;

class Match extends Model
{
	public $_validation = [
		'round_id' => ['required', 'exists:rounds,id'],
		'planned_at' => ['date:YY/mm/dd HH:mm', 'required'],
		'started_at' => ['date:YY/mm/dd HH:mm'],
		'finished_at' => ['date:YY/mm/dd HH:mm'],
		'approved_at' => ['date:YY/mm/dd HH:mm'],
		'approved_by' => ['integer'],
		'disputed_at' => ['date:YY/mm/dd HH:mm'],
	];

	/**
	 * Round in which the match is played
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function round()
	{
		return $this -> belongsTo(__NAMESPACE__.'\Round');
	}

	/**
	 * Competing teams
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function competitors()
	{
		return $this -> hasMany(__CLASS__.'\Competitor');
	}

	/**
	 * Loads all match results
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function matchResults()
	{
		return $this->hasMany(__CLASS__.'\MatchResult');
	}
}