<?php
declare( strict_types=1 );

namespace Lipe\Sniffs\Performance;

use WordPressVIPMinimum\Sniffs\Performance\WPQueryParamsSniff;

/**
 * @author Mat Lipe
 * @since  August 2023
 *
 */
class PostNotInSniff extends WPQueryParamsSniff {
	/**
	 * Current stack pointer.
	 *
	 * @var int
	 */
	protected $stackPtr;


	/**
	 * Include object operators in the list of tokens to check.
	 *
	 * Adds support for checking fluent interfaces such as:
	 * - johnbillion/args
	 * - lipemat/wp-libs
	 *
	 * @return array
	 */
	public function register() : array {
		$tokens = parent::register();
		$tokens[] = T_OBJECT_OPERATOR;
		return $tokens;
	}
}
