<?php namespace Syn\Cup\Classes;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Iterator;

class RoundIterator implements Iterator
{

	public function __construct($rounds = [])
	{
		$this->fill($rounds);
	}
	/**
	 * Rounds
	 * @var array
	 */
	protected $_rounds = [];

	/**
	 * Current round
	 * @var int
	 */
	protected $_current = 0;

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Return the current element
	 *
	 * @link http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 */
	public function current()
	{
		return array_get($this->_rounds, $this->_current);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Move forward to next element
	 *
	 * @link http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 */
	public function next()
	{
		$this->_current++;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Return the key of the current element
	 *
	 * @link http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 */
	public function key()
	{
		return $this->_current;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Checks if current position is valid
	 *
	 * @link http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 *       Returns true on success or false on failure.
	 */
	public function valid()
	{
		return in_array($this->_current, $this->_rounds) && $this->current()->planned_at <= Carbon::now();
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Rewind the Iterator to the first element
	 *
	 * @link http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 */
	public function rewind()
	{
		if(in_array(1, $this->_rounds))
			$this->_current = 1;
		else
			$this->_current = 0;
	}

	/**
	 * Fill rounds
	 * @param array $rounds
	 */
	public function fill($rounds = [])
	{
		foreach($rounds as $round)
		{
			// increment starting from 1 instead of 0
			$this->_rounds[$round -> round_no] = $round;
			// sets current round pointer
			if(!$round->finished && $this->_current == 0)
				$this->_current = $round -> round_no;
		}
	}

	public function getRounds()
	{
		return new Collection($this->_rounds);
	}
}