<?php
/**
 * Lipe.Performance.SlowMetaQuery
 *
 * @package Lipe
 */

namespace Lipe\Sniffs\Performance;

use Lipe\Traits\ArrayHelpers;
use Lipe\Traits\ObjectHelpers;
use Lipe\Traits\VariableHelpers;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;
use WordPressCS\WordPress\AbstractArrayAssignmentRestrictionsSniff;

/**
 * Checks `meta_query` for compares which are not performant.
 *
 * - `EXISTS` ok
 * - `NOT_EXISTS` ok
 * The rest are NOT ok.
 *
 * Inspired by Human Made's SlowMetaQuery sniff.
 *
 * @link   https://github.com/humanmade/coding-standards/blob/master/HM/Sniffs/Performance/SlowMetaQuerySniff.php
 *
 * @author Mat Lipe
 * @since  3.1.0
 *
 * @code   `NonPerformant` - For slow meta queries.
 * @code   `DynamicValue` - For dynamic values.
 */
class SlowMetaQuerySniff extends AbstractArrayAssignmentRestrictionsSniff {
	use ArrayHelpers;
	use ObjectHelpers;
	use VariableHelpers;

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
			'slow_query' => [
				'type'    => 'error',
				'message' => 'Querying by %s is not performant.',
				'keys'    => [
					'meta_query',
					'meta_value',
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

		// Check for fluent interface use.
		if ( $this->is_object_assignment( $stackPtr ) ) {
			$prop = $this->phpcsFile->findNext( \T_STRING, ( $stackPtr + 1 ) );

			// Object assignment of meta_value.
			if ( 'meta_value' === $this->tokens[ $prop ]['content'] ) {
				$value = $this->phpcsFile->findNext( \T_CONSTANT_ENCAPSED_STRING, ( $prop + 1 ) );
				$this->callback( 'meta_value', $this->strip_quotes( $this->tokens[ $value ]['content'] ), $this->tokens[ $prop ]['line'], $this->groups_cache['slow_query'] );
			} elseif ( 'meta_query' === $this->tokens[ $prop ]['content'] ) {
				// Fluent interface callback.
				if ( T_OPEN_PARENTHESIS === $this->tokens[ $prop + 1 ]['code'] ) {
					$call = $this->phpcsFile->findNext( \T_STRING, ( $prop + 2 ) );
					if ( ! in_array( $this->tokens[ $call ]['content'], [ 'exists', 'not_exists' ], true ) ) {
						$this->addMessage(
							'Using %s comparison in `meta_query` is non-performant.',
							$call,
							true,
							'NonPerformant',
							[ $this->tokens[ $call ]['content'] ]
						);
					}
				} else {
					// Object assignment of meta_query.
					$next = $this->phpcsFile->findNext( array_merge( Tokens::$emptyTokens, [ T_EQUAL, T_DOUBLE_ARROW ] ), $prop + 1, null, true );
					if ( T_VARIABLE === $this->tokens[ $next ]['code'] ) {
						// Attempt to detect a sub fluent interface.
						if ( $this->is_class_object( $next ) ) {
							$compare = $this->get_assigned_properties( $next );
							if ( isset( $compare['compare'] ) ) {
								$this->check_compare_value( $this->tokens[ $compare['compare'] ]['content'], $next );
								return;
							}
						}

						$this->addMessage(
							'Using a dynamic comparison in `meta_query` cannot be checked automatically, and may be non-performant.',
							$prop,
							'warning',
							'Dynamic'
						);
					} else {
						$this->check_meta_query_item( $next );
					}
				}
			}
		}

		unset( $this->stackPtr );
	}


	/**
	 * Callback to process each confirmed key, to check value.
	 * This must be extended to add the logic to check assignment value.
	 *
	 * @param string $key   Array index / key.
	 * @param mixed  $val   Assigned value.
	 * @param int    $line  Token line.
	 * @param array  $group Group definition.
	 *
	 * @return bool
	 */
	public function callback( $key, $val, $line, $group ) : bool {
		switch ( $key ) {
			case 'meta_value':
				return $this->check_meta_compare();

			case 'meta_query':
				return $this->check_meta_query();

			default:
				// Unknown key, assume it's an error.
				return true;
		}
	}


	/**
	 * Check if we have an allowed meta_compare somewhere in the meta_query.
	 *
	 * @return bool
	 */
	protected function check_meta_compare() : bool {
		// Unable to determine if there is a compare somewhere.
		if ( T_DOUBLE_ARROW !== $this->tokens[ $this->stackPtr ]['code'] ) {
			$this->addMessage(
				$this->groups_cache['slow_query']['message'],
				$this->stackPtr + 1,
				false,
				'NonPerformant',
				[ $this->tokens[ $this->stackPtr + 1 ]['content'] ]
			);
			return false;
		}

		$array_open = $this->phpcsFile->findPrevious( static::$array_tokens, $this->stackPtr - 1 );
		$array_bounds = $this->find_array_open_close( $array_open );
		$elements = $this->get_array_indices( $array_bounds['opener'], $array_bounds['closer'] );

		$compare_element = $this->find_key_in_array( $elements, 'meta_compare' );

		if ( empty( $compare_element ) ) {
			$compare = 'default';
		} else {
			$compare = $this->get_static_value_for_element( $compare_element );
		}
		$this->check_compare_value( $compare, $compare_element['value_start'] );

		return false;
	}


	/**
	 * Recursively check a meta_query value.
	 */
	protected function check_meta_query() : bool {
		// Find the value of meta_query, and check it.
		$array_open = $this->phpcsFile->findNext( array_merge( Tokens::$emptyTokens, [ T_COMMA, T_CLOSE_SHORT_ARRAY ] ), $this->stackPtr + 1, null, true );
		$this->check_meta_query_item( $array_open );

		// Disable the built-in warnings.
		return false;
	}


	/**
	 * Check an individual meta_query item.
	 *
	 * @param int $array_open A token pointer for the array open token.
	 */
	protected function check_meta_query_item( int $array_open ) {
		$array_open_token = $this->tokens[ $array_open ];
		if ( T_ARRAY !== $array_open_token['code'] && T_OPEN_SHORT_ARRAY !== $array_open_token['code'] ) {
			$this->addMessage(
				'Using a dynamic comparison in `meta_query` cannot be checked automatically, and may be non-performant.',
				$array_open,
				'warning',
				'Dynamic'
			);

			return;
		}

		$array_bounds = $this->find_array_open_close( $array_open );
		$elements = $this->get_array_indices( $array_bounds['opener'], $array_bounds['closer'] );

		// Is this a "first-order" query?
		// @see WP_Meta_Query::is_first_order_clause.
		$first_order_key = $this->find_key_in_array( $elements, 'key' );
		$first_order_value = $this->find_key_in_array( $elements, 'value' );
		if ( $first_order_key || $first_order_value ) {
			$compare_element = $this->find_key_in_array( $elements, 'compare' );
			if ( ! empty( $compare_element ) ) {
				$compare = $this->get_static_value_for_element( $compare_element );
			}
			if ( empty( $compare ) ) {
				// The default is either IN or = depending on whether value is
				// set, but this only matters for the message.
				$compare = 'default';
			}

			$this->check_compare_value( $compare, $compare_element ? $compare_element['value_start'] : null );
			return;
		}

		foreach ( $elements as $element ) {
			if ( isset( $element['index_start'] ) ) {
				$index = $this->strip_quotes( $this->tokens[ $element['index_start'] ]['content'] );
				if ( 'relation' === strtolower( $index ) ) {
					// Skip 'relation' element.
					continue;
				}
			}

			// Otherwise, recurse.
			$this->check_meta_query_item( $element['value_start'] );
		}
	}


	/**
	 * Add an error if the comparison isn't allowed.
	 *
	 * @param string $compare  Comparison value.
	 * @param int    $stackPtr The position in the stack where the token was found.
	 */
	protected function check_compare_value( string $compare, int $stackPtr = null ) {
		if ( null === $stackPtr ) {
			$stackPtr = $this->stackPtr;
		}
		$compare = $this->strip_quotes( $compare );

		if ( '__dynamic' === $compare ) {
			$this->addMessage(
				'Using a dynamic comparison in `meta_query` cannot be checked automatically, and may be non-performant.',
				$stackPtr,
				'warning',
				'Dynamic'
			);
		} elseif ( 'EXISTS' !== $compare && 'NOT EXISTS' !== $compare ) {
			// Add a message ourselves.
			$this->addMessage(
				'Using %s comparison in `meta_query` is non-performant.',
				$stackPtr,
				true,
				'NonPerformant',
				[ $compare ]
			);
		}
	}
}
