<?php
/**
 * Lipe.PHP.DisallowNullCoalesceInForLoops
 *
 * @package Lipe
 */

namespace Lipe\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Disallow null coalesce in for loops sniff.
 *
 * Prevents issues where variables are not checked for an array
 * but are used as arrays because the null coalesce operator is
 * checking for null only.
 *
 *
 * ```php
 * $foo = 'some string';
 * foreach( $foo ?? [] as $key => $value ){
 *    // Type error: Cannot use string as array.
 * }
 *
 * // Proper
 * $foo = 'some string';
 * if ( is_array( $foo ) ) {
 *    foreach( $foo as $key => $value ){
 *    }
 * }
 *  ```
 *
 * @author      Mat Lipe
 * @since       3.2.0
 *
 * @code        `Ternary` - For ternary in for loops.
 * @code        `Coalesce` - For null coalesce in for loops.
 * @code        `CoalesceEqual` - For null coalesce equal in for loops.
 */
class DisallowNullCoalesceInForLoopsSniff implements Sniff {
	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var list<string>
	 */
	public $supportedTokenizers = [ 'PHP' ];

	/**
	 * A list of tokens that which start a for statement.
	 *
	 * @var list<int>
	 */
	protected $disallowedStartTokens = [
		\T_FOREACH,
		\T_FOR,
	];


	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array<string|int>
	 */
	public function register(): array {
		$tokens = [
			\T_INLINE_ELSE,
		];
		// PHP 7.0+.
		if ( \defined( 'T_COALESCE' ) ) {
			$tokens[] = \T_COALESCE;
		}
		// PHP 7.4+.
		if ( \defined( 'T_COALESCE_EQUAL' ) ) {
			$tokens[] = \T_COALESCE_EQUAL;
		}
		return $tokens;
	}


	/**
	 * Process the tokens that this sniff is listening for.
	 *
	 * @param File $phpcsFile                        The file where the token was found.
	 * @param int  $stackPtr                         The position in the stack where
	 *                                               the token was found.
	 *
	 * @return void
	 */
	public function process( File $phpcsFile, $stackPtr ): void {
		$tokens = $phpcsFile->getTokens();
		$skip_in_statement = [
			\T_OPEN_PARENTHESIS,
			\T_OPEN_SQUARE_BRACKET,
		];
		$statement_start = $phpcsFile->findStartOfStatement( $stackPtr, $skip_in_statement );

		$error = false;
		if ( \in_array( $tokens[ $statement_start ]['code'], $this->disallowedStartTokens, true ) ) {
			$error = true;
		} elseif ( isset( $tokens[ $statement_start ]['nested_parenthesis'] ) ) {
			$parenthesis = $tokens[ $statement_start ]['nested_parenthesis'];
			foreach ( $parenthesis as $start => $end ) {
				$previous = $phpcsFile->findPrevious( \T_WHITESPACE, ( $start - 1 ), null, true );
				if ( false !== $previous && \T_FOR === $tokens[ $previous ]['code'] ) {
					$error = true;
					break;
				}
			}
		}

		if ( $error ) {
			if ( \T_INLINE_ELSE === $tokens[ $stackPtr ]['code'] ) {
				$phpcsFile->addWarning(
					'Using ternary in a for loop is not allowed.',
					$stackPtr,
					'Ternary'
				);
				return;
			}
			if ( \T_COALESCE_EQUAL === $tokens[ $stackPtr ]['code'] ) {
				$phpcsFile->addWarning(
					'Using null coalesce equal in a for loop is not allowed.',
					$stackPtr,
					'CoalesceEqual'
				);
				return;
			}
			$phpcsFile->addWarning(
				'Using null coalesce in a for loop is not allowed.',
				$stackPtr,
				'Coalesce'
			);
		}
	}
}
