<?php

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
 *
 * @author Mat Lipe
 * @since  3.0.0
 *
 * @code   `TernaryFound` - For ternary in condition.
 * @code   `CoalesceFound` - For null coalesce in condition.
 * @code   `CoalesceEqualFound` - For null coalesce equal in condition.
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
	 * @return array
	 */
	public function register() {
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

		$skipTokens = Tokens::$emptyTokens;
		$skipTokens[] = \T_OPEN_PARENTHESIS;

		$prev = $phpcsFile->findPrevious( $skipTokens, ( $stackPtr - 1 ), null, true );

		if ( false === $prev ) {
			// Live coding or parse error.
			return;
		}

		$prev_statement_closer = $phpcsFile->findStartOfStatement( $stackPtr, [ \T_COLON, \T_OPEN_PARENTHESIS, \T_OPEN_SQUARE_BRACKET ] );

		if ( \T_IF === $tokens[ $prev_statement_closer ]['code'] ) {
			if ( \T_INLINE_ELSE === $tokens[ $stackPtr ]['code'] ) {
				$phpcsFile->addError(
					'Using ternary in a condition is not allowed.',
					$stackPtr,
					'TernaryFound'
				);
			}
			if ( \T_COALESCE_EQUAL === $tokens[ $stackPtr ]['code'] ) {
				$phpcsFile->addError(
					'Using null coalesce equal in a condition is not allowed.',
					$stackPtr,
					'CoalesceEqualFound'
				);
			}
			$phpcsFile->addError(
				'Using null coalesce in a condition is not allowed.',
				$stackPtr,
				'CoalesceFound'
			);
		}
	}

}
