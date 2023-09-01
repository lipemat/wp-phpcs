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
use PHPCSUtils\Utils\Arrays;
use PHPCSUtils\Utils\MessageHelper;
use PHPCSUtils\Utils\TextStrings;
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
 *
 * @phpstan-type Group array{keys:array<int, string>, message:string, type:'error'|'warning'}
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
	 * @return array<int|string>
	 */
	public function register() : array {
		$tokens = parent::register();
		$tokens[] = T_OBJECT_OPERATOR;
		return $tokens;
	}


	/**
	 * Groups of variables to restrict.
	 *
	 * @return array<string, Group>
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
	public function process_token( $stackPtr ) : void {
		$this->stackPtr = $stackPtr;
		parent::process_token( $stackPtr );

		// Check for fluent interface use.
		if ( ! $this->is_object_assignment( $stackPtr ) ) {
			return;
		}
		$prop = $this->phpcsFile->findNext( \T_STRING, ( $stackPtr + 1 ) );
		if ( false === $prop ) {
			return;
		}

		// Object assignment of meta_value.
		if ( 'meta_value' === $this->tokens[ $prop ]['content'] ) {
			$value = $this->phpcsFile->findNext( \T_CONSTANT_ENCAPSED_STRING, ( $prop + 1 ) );
			$this->callback( 'meta_value', TextStrings::stripQuotes( $this->tokens[ $value ]['content'] ), $this->tokens[ $prop ]['line'], $this->groups_cache['slow_query'] );
		} elseif ( 'meta_query' === $this->tokens[ $prop ]['content'] ) {
			// Fluent interface callback.
			if ( T_OPEN_PARENTHESIS === $this->tokens[ $prop + 1 ]['code'] ) {
				$call = $this->phpcsFile->findNext( \T_STRING, ( $prop + 2 ) );
				if ( ! in_array( $this->tokens[ $call ]['content'], [ 'exists', 'not_exists' ], true ) ) {
					MessageHelper::addMessage(
						$this->phpcsFile,
						'Using %s comparison in `meta_query` is non-performant.',
						false === $call ? $prop : $call,
						true,
						'NonPerformant',
						[ $this->tokens[ $call ]['content'] ]
					);
				}
			} else {
				// Object assignment of meta_query.
				$next = $this->phpcsFile->findNext( array_merge( Tokens::$emptyTokens, [ T_EQUAL, T_DOUBLE_ARROW ] ), $prop + 1, null, true );
				if ( false === $next ) {
					$this->check_compare_value( '__dynamic', $prop );
				} elseif ( T_VARIABLE === $this->tokens[ $next ]['code'] ) {
					// Attempt to detect a sub fluent interface.
					if ( $this->is_class_object( $next ) ) {
						$compare = $this->get_assigned_properties( $next );
						if ( isset( $compare['compare'] ) ) {
							$this->check_compare_value( $this->tokens[ $compare['compare'] ]['content'], $next );
							return;
						}
					} elseif ( $this->is_variable_an_array( $next ) ) {
						$compare = $this->find_key_in_array( $next, 'compare' );
						if ( false !== $compare ) {
							$compare = $this->get_static_value_from_variable( $compare );
							if ( null !== $compare ) {
								$this->check_compare_value( $compare, $next );
								return;
							}
						}
					}
					$this->check_compare_value( '__dynamic', $prop );
				} else {
					$this->check_meta_query_item( $next );
				}
			}
		}
	}


	/**
	 * Callback to process each confirmed key, to check value.
	 * This must be extended to add the logic to check assignment value.
	 *
	 * @param string       $key   Array index / key.
	 * @param mixed        $val   Assigned value.
	 * @param int          $line  Token line.
	 * @param array<mixed> $group Group definition.
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
			MessageHelper::addMessage(
				$this->phpcsFile,
				$this->groups_cache['slow_query']['message'],
				$this->stackPtr + 1,
				false,
				'NonPerformant',
				[ $this->tokens[ $this->stackPtr + 1 ]['content'] ]
			);
			return false;
		}

		$array_open = $this->phpcsFile->findPrevious( static::$array_tokens, $this->stackPtr - 1 );
		if ( false !== $array_open ) {
			$compare_element = $this->find_key_in_array( $array_open, 'meta_compare' );
			if ( false !== $compare_element ) {
				$compare = $this->get_static_value_for_element( $compare_element );
				$this->check_compare_value( (string) $compare, $compare_element );
				return false;
			}
		}
		$this->check_compare_value( 'default', $this->stackPtr + 1 );

		// Disable the built-in warnings.
		return false;
	}


	/**
	 * Recursively check a meta_query value.
	 */
	protected function check_meta_query() : bool {
		$array_open = $this->phpcsFile->findNext( array_merge( Tokens::$emptyTokens, [ T_COMMA, T_CLOSE_SHORT_ARRAY ] ), $this->stackPtr + 1, null, true );
		if ( false !== $array_open ) {
			$this->check_meta_query_item( $array_open );
		}

		// Disable the built-in warnings.
		return false;
	}


	/**
	 * Check an individual meta_query item.
	 *
	 * @param int $array_open A token pointer for the array open token.
	 *
	 * @return void
	 */
	protected function check_meta_query_item( int $array_open ) : void {
		$array_open_token = $this->tokens[ $array_open ];
		if ( T_ARRAY !== $array_open_token['code'] && T_OPEN_SHORT_ARRAY !== $array_open_token['code'] ) {
			MessageHelper::addMessage(
				$this->phpcsFile,
				'Using a dynamic comparison in `meta_query` cannot be checked automatically, and may be non-performant.',
				$array_open,
				false,
				'Dynamic'
			);

			return;
		}

		$array_bounds = Arrays::getOpenClose( $this->phpcsFile, $array_open );
		if ( false === $array_bounds || ! isset( $array_bounds['opener'], $array_bounds['closer'] ) ) {
			return;
		}
		$elements = $this->get_array_indices( $array_bounds['opener'], $array_bounds['closer'] );

		// Is this a "first-order" query?
		// @see WP_Meta_Query::is_first_order_clause.
		$first_order_key = $this->find_key_in_array_elements( $elements, 'key' );
		$first_order_value = $this->find_key_in_array_elements( $elements, 'value' );
		if ( null !== $first_order_key || null !== $first_order_value ) {
			$compare_element = $this->find_key_in_array_elements( $elements, 'compare' );
			$token = null;
			if ( null !== $compare_element && false !== $compare_element['value_start'] ) {
				$compare = (string) $this->get_static_value_for_element( $compare_element['value_start'] );
				$token = $compare_element['value_start'];
			} else {
				$compare = 'default';
			}

			$this->check_compare_value( $compare, $token );
			return;
		}

		foreach ( $elements as $element ) {
			if ( isset( $element['index_start'] ) ) {
				$index = TextStrings::stripQuotes( $this->tokens[ $element['index_start'] ]['content'] );
				if ( 'relation' === strtolower( $index ) ) {
					continue;
				}
			}

			if ( false !== $element['value_start'] ) {
				$this->check_meta_query_item( $element['value_start'] );
			}
		}
	}


	/**
	 * Add an error if the comparison isn't allowed.
	 *
	 * @param string   $compare  Comparison value.
	 * @param int|null $stackPtr The position in the stack where the token was found.
	 *
	 * @return void
	 */
	protected function check_compare_value( string $compare, int $stackPtr = null ) : void {
		if ( null === $stackPtr ) {
			$stackPtr = $this->stackPtr;
		}
		$compare = TextStrings::stripQuotes( $compare );

		if ( '__dynamic' === $compare ) {
			MessageHelper::addMessage(
				$this->phpcsFile,
				'Using a dynamic comparison in `meta_query` cannot be checked automatically, and may be non-performant.',
				$stackPtr,
				false,
				'Dynamic'
			);
		} elseif ( 'EXISTS' !== $compare && 'NOT EXISTS' !== $compare ) {
			// Add a message ourselves.
			MessageHelper::addMessage(
				$this->phpcsFile,
				'Using %s comparison in `meta_query` is non-performant.',
				$stackPtr,
				true,
				'NonPerformant',
				[ $compare ]
			);
		}
	}
}
