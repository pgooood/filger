<?php
namespace pgood\form;

abstract class scheme{
	/**
	 * Adds value to a queue for future saving
	 * @param \pgood\form\uri $uri
	 * @param mixed $value
	 */
	abstract public function add(uri $uri,$value);
	/**
	 * Save values from the queue
	 */
	abstract public function save();
	/**
	 * Clear the queue
	 */
	abstract public function clear();
	/**
	 * Returns value
	 */
	abstract public function value(\pgood\form\uri $uri);
}

