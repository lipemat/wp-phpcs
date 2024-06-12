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
	public function get_stuck(): string {
		return static::$stuck;
	}


	protected const FOO = 'foo';


	/**
	 * Get the value of class constant.
	 *
	 * @return string
	 */
	public function get_foo(): string {
		static::get_stuck_static();
		return self::FOO;
	}


	/**
	 * Get stuck static.
	 *
	 * @return string
	 */
	protected static function get_stuck_static(): string {
		return static::FOO;
	}
}
