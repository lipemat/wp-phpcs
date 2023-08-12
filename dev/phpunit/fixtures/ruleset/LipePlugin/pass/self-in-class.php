<?php
/**
 * Noop functions for load-scripts.php and load-styles.php.
 *
 * @since      4.4.0
 * @subpackage Administration
 * @package    WordPress
 */

/**
 * Noop functions for load-scripts.php and load-styles.php.
 *
 * @since      4.4.0
 * @subpackage Administration
 * @package    WordPress
 */
class Nope {
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
	public function get_stuck() : string {
		return static::$stuck;
	}
}
