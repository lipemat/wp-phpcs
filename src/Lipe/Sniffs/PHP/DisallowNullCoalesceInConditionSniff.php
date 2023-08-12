<?php
/**
 * Lipe.PHP.DisallowNullCoalesceInCondition
 *
 * @package Lipe
 */

namespace Lipe\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Disallow null coalesce in condition sniff.
 *
 * Prevents using null coalesce in conditions.
 *
 * ```php
 * // Failures
 * if ( $foo ?? false )
 * if ( $foo ?: false )
 * if ( $foo ? true : false )
 * if ( $post['post_type'] ?? 'page' === 'page' )
 * if ( $post['post_type'] ??= 'page' === 'page' )
 * if ( 0 !== $update_id ? 'updated' : 'added' )
 *
 * // Pass
 * 0 !== $update_id ? 'updated' : 'added'
 *
 * @author Mat Lipe
 * @since  3.0.0
 *
 * @code   `Ternary` - For ternary in condition.
 * @code   `Coalesce` - For null coalesce in condition.
 * @code   `CoalesceEqual` - For null coalesce equal in condition.
 */
class DisallowNullCoalesceInConditionSniff implements Sniff {
	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var string[]
	 */
	public $supportedTokenizers = [ 'PHP' ];


	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array<string|int>
	 */
	public function register() : array {
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
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();
		$skip_in_statement = [
			\T_COLON,
			\T_OPEN_PARENTHESIS,
			\T_OPEN_SQUARE_BRACKET,
		];
		$statement_start = $phpcsFile->findStartOfStatement( $stackPtr, $skip_in_statement );
		$statement_end = $phpcsFile->findEndOfStatement( $stackPtr, $skip_in_statement );

		$error = false;
		if ( \T_IF === $tokens[ $statement_start ]['code'] ) {
			$error = true;
			// We allow ternary in conditions if it is outside an if statement.
		} elseif ( \T_INLINE_ELSE !== $tokens[ $stackPtr ]['code'] ) {
			// Loop through the tokens with the statement and check for equality tokens.
			for ( $i = $statement_start; $i < $statement_end; $i ++ ) {
				if ( \in_array( $tokens[ $i ]['code'], Tokens::$equalityTokens, true ) ) {
					$error = true;
					break;
				}
			}
		}

		if ( $error ) {
			if ( \T_INLINE_ELSE === $tokens[ $stackPtr ]['code'] ) {
				$phpcsFile->addError(
					'Using ternary in a condition is not allowed.',
					$stackPtr,
					'Ternary'
				);
				return;
			}
			if ( \T_COALESCE_EQUAL === $tokens[ $stackPtr ]['code'] ) {
				$phpcsFile->addError(
					'Using null coalesce equal in a condition is not allowed.',
					$stackPtr,
					'CoalesceEqual'
				);
				return;
			}
			$phpcsFile->addError(
				'Using null coalesce in a condition is not allowed.',
				$stackPtr,
				'Coalesce'
			);
		}
	}

}
