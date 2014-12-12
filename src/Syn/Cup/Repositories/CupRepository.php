<?php namespace Syn\Cup\Repositories;

use Carbon\Carbon;
use Syn\Cup\Interfaces\CupRepositoryInterface;
use Syn\Framework\Abstracts\Repository;

class CupRepository extends Repository implements CupRepositoryInterface
{


	/**
	 * Loads next upcoming cups
	 *
	 * @return array
	 */
	public function findUpcoming()
	{
		// TODO: Implement findUpcoming() method.
	}

	/**
	 * Loads currently running cups
	 *
	 * @return array
	 */
	public function findRunning()
	{
		// TODO: Implement findRunning() method.
	}

	/**
	 * Loads last finished cups
	 *
	 * @return array
	 */
	public function findFinished()
	{
		// TODO: Implement findFinished() method.
	}

	/**
	 * Returns any cup that is running, is upcoming, should be finished etc.
	 */
	public function findNeedAttention()
	{
		return $this -> model
			// starts within an hour
			-> where('starts_at', '<=', Carbon::now()->addHour())
			// cannot be finished
			-> whereNull('finished_at')
			-> get();
	}
}