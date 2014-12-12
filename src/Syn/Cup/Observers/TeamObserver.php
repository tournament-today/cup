<?php namespace Syn\Cup\Observers;


class TeamObserver
{
	public function deleted($model)
	{
		$model -> members() -> delete();
	}

	public function restored($model)
	{
		$model -> members() -> restore();
	}
}