<?php
/**
 * Noop functions for load-scripts.php and load-styles.php.
 *
 * @since      4.4.0
 * @subpackage Administration
 * @package    WordPress
 */

namespace Test;

/**
 * Noop functions for load-scripts.php and load-styles.php.
 *
 * @since      4.4.0
 * @subpackage Administration
 * @package    WordPress
 */
class Yep {
	protected const FOO = 'bar';
	/**
	 * Stuck.
	 *
	 * @var string
	 */
	protected static $stuck = 'foo';


	/**
	 * Get stuck.
	 *
	 * @return string
	 */
	public function get_stuck(): string {
		return static::$stuck;
	}


	/**
	 * Get foo constant
	 *
	 * @return string
	 */
	public function get_foo(): string {
		return static::FOO;
	}
}
