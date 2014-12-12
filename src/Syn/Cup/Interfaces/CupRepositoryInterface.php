<?php namespace Syn\Cup\Interfaces;

use Syn\Framework\Abstracts\RepositoryInterface;

interface CupRepositoryInterface extends RepositoryInterface
{
	/**
	 * Loads next upcoming cups
	 * @return array
	 */
	public function findUpcoming();

	/**
	 * Loads currently running cups
	 * @return array
	 */
	public function findRunning();

	/**
	 * Loads last finished cups
	 * @return array
	 */
	public function findFinished();
}