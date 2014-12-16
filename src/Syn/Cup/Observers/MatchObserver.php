<?php namespace Syn\Cup\Observers;

use Carbon\Carbon;
use Config;
use Syn\Framework\Exceptions\UnexpectedResultException;
use Syn\Vm\Models\Vm;

class MatchObserver
{
	public function created($model)
	{
		if($model->planned_at > Carbon::now())
		{
			$vm = new Vm;
			$vm -> match_id = $model->id;
			$vm -> cup_id = $model->round->cup_id;
			$vm -> round_id = $model->round_id;
			$vm -> provider = Config::get('vm::provider');
			// unused
			$vm -> preferred_memory = 0;
			// unused
			$vm -> preferred_cpus = 0;
			if(!$vm -> save())
				throw new UnexpectedResultException("Could not create Vm instance for match {$model->id}");
		}
	}
}