<?php
/**
 * Helpers for working with variables.
 *
 * @since   3.1.0
 * @package Lipe
 */

namespace Lipe\Traits;

use PHP_CodeSniffer\Util\Tokens;
use VariableAnalysis\Lib\Helpers;

/**
 * Helpers for working with variables.
 *
 * @author Mat Lipe
 * @since  3.1.0
 */
trait VariableHelpers {

	/**
	 * Get the position of a variable assignment based
	 * on the variable usage.
	 *
	 * @param int $token - Position of the variable usage.
	 *
	 * @return int|false
	 */
	protected function get_variable_assignment( int $token ) {
		$content = $this->tokens[ $token ]['content'];
		$stackPtr = $token;

		// This is the assignment statement.
		$next = $this->phpcsFile->findNext( Tokens::$emptyTokens, $stackPtr + 1, null, true, null, true );
		if ( $next && T_EQUAL === $this->tokens[ $next ]['code'] ) {
			return $stackPtr;
		}

		while ( $stackPtr > 0 && ! empty( $stackPtr ) ) {
			$stackPtr = $this->phpcsFile->findPrevious( T_VARIABLE, $stackPtr - 1, null, false, $content );

			if ( ! $stackPtr ) {
				return false;
			}

			$next = $this->phpcsFile->findNext( Tokens::$emptyTokens, $stackPtr + 1, null, true, null, true );
			if ( $next && T_EQUAL === $this->tokens[ $next ]['code'] ) {
				return $stackPtr;
			}

			if ( Helpers::isTokenFunctionParameter( $this->phpcsFile, $stackPtr ) ) {
				return $stackPtr;
			}
		}

		return false;
	}
}
