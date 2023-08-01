<?php
/**
 * Lipe.DB.SlowOrderBy
 *
 * @package Lipe
 */

namespace Lipe\Sniffs\DB;

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
