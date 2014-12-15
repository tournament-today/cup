<?php namespace Syn\Cup\Observers;

use Queue;
use Syn\Notification\Classes\HipChat;
use Syn\Notification\Models\Notification;

class CupObserver
{
	public function deleted($model)
	{
		$memberCount = $model->teamMembers->count();
		foreach($model->teamMembers as $member)
		{
			$notification = new Notification;
			$notification -> unguard();
			$notification -> fill([
				'title' => trans('cup.inform-cup-deleted', $model->getAttributes()),
				'receiver_id' => $member -> gamer_id
			]);
			$notification -> save();
		}

		Queue::push(function($job) use ($model, $memberCount)
		{
			HipChat::messageRoom("Cup deleted: {$model->name}, participants informed: {$memberCount}");
			$job->delete();
		});
	}
}