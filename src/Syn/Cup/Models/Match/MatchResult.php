<?php namespace Syn\Cup\Models\Match;

use Syn\Framework\Abstracts\Model;

class MatchResult extends Model
{
	public $_validation = [
		'match_id' => ['required', 'exists:matches,id'],
		'competitor_id' => ['required', 'exists:competitors,id'],
		'gamer_id' => ['required', 'exists:gamers,id'],
		'trustworthiness' => ['required', 'integer'],
		'score' => ['required', 'integer'],
		'score_image' => ['image'],
	];

	public function round()
	{
		return $this -> belongsTo(__NAMESPACE__.'\Round');
	}
	public function cup()
	{
		return $this -> belongsTo(__NAMESPACE__.'\Cup');
	}
}