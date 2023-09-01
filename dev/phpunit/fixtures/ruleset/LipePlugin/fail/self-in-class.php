<?php

class Nope {
	private static $stuck = 'foo';


	public function get_stuck(): string {
		return self::$stuck;
	}


	private static function get_stuck_static(): string {
		return self::$stuck;
	}


	public function call(): string {
		return self::get_stuck_static();
	}
}

/**
 * Some comments.
 *
 * @notice Constants in a final class cannot be static.
 */
final class NopeFinal {
	/**
	 * Some comments.
	 *
	 * @notice Constants in a final class cannot be static.
	 */
	private static $stuck = 'foo';


	/**
	 * Get stuck.
	 *
	 * @return string
	 */
	public function get_stuck(): string {
		return self::$stuck;
	}


	/**
	 * Get stuck static.
	 *
	 * @return string
	 */
	private static function get_stuck_static(): string {
		return self::$stuck;
	}


	/**
	 * Call.
	 *
	 * @return string
	 */
	public function call(): string {
		return self::get_stuck_static();
	}
}
