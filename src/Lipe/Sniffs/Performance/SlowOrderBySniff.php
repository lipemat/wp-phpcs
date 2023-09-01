<?php
/**
 * Lipe.Performance.SlowOrderBy
 *
 * @package Lipe
 */

namespace Lipe\Sniffs\Performance;

use Lipe\Traits\ArrayHelpers;
use Lipe\Traits\ObjectHelpers;
use Lipe\Traits\VariableHelpers;
use PHPCSUtils\Utils\MessageHelper;
use PHPCSUtils\Utils\TextStrings;
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
 *
 * @phpstan-type Group array{keys:array<int, string>, message:string, type:'error'|'warning'}
 */
class SlowOrderBySniff extends AbstractArrayAssignmentRestrictionsSniff {
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
	 * @return array<string|int>
	 */
	public function register(): array {
		$tokens = parent::register();
		$tokens[] = T_OBJECT_OPERATOR;
		return $tokens;
	}


	/**
	 * Groups of variables to restrict.
	 *
	 * @return array<string, Group>
	 */
	public function getGroups(): array {
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
	public function process_token( $stackPtr ): void {
		$this->stackPtr = $stackPtr;
		parent::process_token( $stackPtr );

		// Check if a fluent interface is using the parameters.
		if ( $this->is_object_assignment( $stackPtr ) ) {
			$prop = $this->phpcsFile->findNext( \T_OPEN_CURLY_BRACKET, ( $stackPtr + 1 ), null, true );
			if ( false !== $prop && 'orderby' === $this->tokens[ $prop ]['content'] ) {
				$value = $this->phpcsFile->findNext( \T_CONSTANT_ENCAPSED_STRING, ( $prop + 1 ) );
				$this->callback( 'orderby', $this->tokens[ $value ]['content'], $this->tokens[ $prop ]['line'], $this->groups_cache['slow_orderby'] );
			}
		}

		unset( $this->stackPtr );
	}


	/**
	 * Callback to process each confirmed key, to check value.
	 *
	 * @param string       $key   Array index / key.
	 * @param mixed        $val   Assigned value.
	 * @param int          $line  Token line.
	 * @param array<mixed> $group Group definition.
	 *
	 * @return bool
	 */
	public function callback( $key, $val, $line, $group ): bool {
		if ( is_string( $val ) ) {
			$val = TextStrings::stripQuotes( $val );
		}
		switch ( $val ) {
			case 'rand':
			case 'meta_value':
			case 'meta_value_num':
				MessageHelper::addMessage(
					$this->phpcsFile,
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
