<?php namespace Syn\Cup\Observers;

use Queue;

class MemberObserver
{
	/**
	 * Queues the mail to invite on creation
	 * @param $model
	 */
	public function created($model)
	{
		if(!$model->accepted_at)
			Queue::push('Syn\Cup\Tasks\InviteTask@send', [
				'id' => $model -> id
			]);
	}

	/**
	 * @info removes a team of no members are left
	 * @param $model
	 */
	public function deleted($model)
	{
		if(!count($model->team->members))
			$model->team->delete();
	}
}