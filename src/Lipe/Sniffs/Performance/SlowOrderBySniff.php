<?php
/**
 * Lipe.Performance.SlowOrderBy
 *
 * @package Lipe
 */

namespace Lipe\Sniffs\Performance;

use WordPressCS\WordPress\AbstractArrayAssignmentRestrictionsSniff;

/**
 * Checks the WP_Query for slow orderby clauses.
 *
 * - `rand` NOT ok.
 * - `meta_value` NOT ok.
 * - `meta_value_num` NOT ok.
 *
 * Inspired by Human Made's SlowOrderBy sniff.
 *
 * @link   https://github.com/humanmade/coding-standards/blob/master/HM/Sniffs/Performance/SlowOrderBySniff.php
 *
 * @author Mat Lipe
 * @since  3.1.0
 *
 * @code   `rand`
 * @code   `meta_value`
 * @code   `meta_value_num`
 */
final class SlowOrderBySniff extends AbstractArrayAssignmentRestrictionsSniff {
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


	/**
	 * Groups of variables to restrict.
	 *
	 * @return array
	 */
	public function getGroups() : array {
		return [
			'slow_orderby' => [
				'type'    => 'error',
				'message' => 'Ordering query results by %s is not performant.',
				'keys'    => [
					'orderby',
				],
			],
		];
	}


	/**
	 * Process a token.
	 *
	 * Overrides the parent to store the stackPtr for later use.
	 *
	 * @param int $stackPtr - Current position in the stack.
	 */
	public function process_token( $stackPtr ) {
		$this->stackPtr = $stackPtr;
		parent::process_token( $stackPtr );

		// Check for fluent interface use of the parameters.
		if ( T_OBJECT_OPERATOR === $this->tokens[ $stackPtr ]['code'] ) {
			$prop = $this->phpcsFile->findNext( \T_OPEN_CURLY_BRACKET, ( $stackPtr + 1 ), null, true );
			if ( 'orderby' === $this->tokens[ $prop ]['content'] ) {
				$value = $this->phpcsFile->findNext( \T_CONSTANT_ENCAPSED_STRING, ( $prop + 1 ) );
				$this->callback( 'orderby', $this->strip_quotes( $this->tokens[ $value ]['content'] ), $this->tokens[ $prop ]['line'], $this->groups_cache['slow_orderby'] );
			}
		}

		unset( $this->stackPtr );
	}


	/**
	 * Callback to process each confirmed key, to check value.
	 *
	 * @param string $key   Array index / key.
	 * @param mixed  $val   Assigned value.
	 * @param int    $line  Token line.
	 * @param array  $group Group definition.
	 *
	 * @return bool
	 */
	public function callback( $key, $val, $line, $group ) : bool {
		switch ( $val ) {
			case 'rand':
			case 'meta_value':
			case 'meta_value_num':
				$this->addMessage(
					'Ordering query results by %s is not performant.',
					$this->stackPtr,
					true,
					$val,
					[ $val ]
				);
				return false;
			default:
				// No match.
				return false;
		}
	}
}
