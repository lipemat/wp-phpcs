<?php
/**
 * Helpers for working with objects.
 *
 * @since   3.1.0
 * @package Lipe
 */

namespace Lipe\Traits;

use PHP_CodeSniffer\Util\Tokens;
use WordPressCS\WordPress\AbstractFunctionRestrictionsSniff;

/**
 * Helpers for working with objects.
 *
 * @author   Mat Lipe
 * @since    3.1.0
 *
 * @property $phpcsFile File
 * @property $tokens    array
 */
trait ObjectHelpers {
	/**
	 * Override the parent method to also exclude `new` statements
	 * for things like `new Get_Posts()`.
	 *
	 * @see AbstractFunctionRestrictionsSniff::is_targetted_token()
	 *
	 * @param int $stackPtr - The position of the current token in the stack.
	 *
	 * @return bool
	 */
	public function is_targetted_token( $stackPtr ) : bool {
		if ( method_exists( parent::class, 'is_targetted_token' ) && ! parent::is_targetted_token( $stackPtr ) ) {
			return false;
		}
		$prev = $this->phpcsFile->findPrevious( Tokens::$emptyTokens, ( $stackPtr - 1 ), null, true, null, true );
		return T_NEW !== $this->tokens[ $prev ]['code'];
	}


	/**
	 * Is this variable or `->` part of an object assigment created
	 * most likely by a fluent interface.
	 *
	 * @param int $token - Position of the variable usage.
	 *
	 * @return bool
	 */
	protected function is_object_assignment( int $token ) : bool {
		if ( T_VARIABLE === $this->tokens[ $token ]['code'] ) {
			$variable = $token;
			$next = $this->phpcsFile->findNext( Tokens::$emptyTokens, $token + 1, null, true, null, true );
			if ( ! $next || T_OBJECT_OPERATOR !== $this->tokens[ $next ]['code'] ) {
				return false;
			}
		} elseif ( T_OBJECT_OPERATOR === $this->tokens[ $token ]['code'] ) {
			$prev = $this->phpcsFile->findPrevious( Tokens::$emptyTokens, $token + - 1, null, true, null, true );
			if ( ! $prev || T_VARIABLE !== $this->tokens[ $prev ]['code'] ) {
				return false;
			}
			$variable = $prev;
		} else {
			return false;
		}

		$assignment = $this->get_variable_assignment( $variable );
		if ( false === $assignment ) {
			return false;
		}
		return false !== $this->phpcsFile->findNext( [ T_NEW ], $assignment, null, false, null, true );
	}


	/**
	 * Get the properties assigned to an object.
	 *
	 * @param int $token - Position of the variable usage.
	 *
	 * @return string[]
	 */
	protected function get_assigned_properties( int $token ) : array {
		$assignment = $this->get_variable_assignment( $token );
		if ( false === $assignment ) {
			return [];
		}

		$properties = [];
		$stackPtr = $assignment;
		while ( $stackPtr > 0 && ! empty( $stackPtr ) && $stackPtr < $token ) {
			$stackPtr = $this->phpcsFile->findNext( T_VARIABLE, $stackPtr + 1, null, false, $this->tokens[ $token ]['content'] );
			if ( ! $stackPtr ) {
				break;
			}
			$operator = $this->phpcsFile->findNext( Tokens::$emptyTokens, $stackPtr + 1, null, true, null, true );
			if ( T_OBJECT_OPERATOR !== $this->tokens[ $operator ]['code'] ) {
				continue;
			}

			$property = $this->phpcsFile->findNext( Tokens::$emptyTokens, $stackPtr + 2, null, true, null, true );
			if ( ! $property ) {
				continue;
			}

			$value = $this->phpcsFile->findNext( array_merge( [ T_EQUAL ], Tokens::$emptyTokens ), $property + 1, null, true, null, true );
			if ( ! $value ) {
				continue;
			}

			$properties[ $this->tokens[ $property ]['content'] ] = $value;
		}

		return $properties;
	}
}
