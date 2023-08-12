<?php
/**
 * Helpers for working with objects.
 *
 * @since   3.1.0
 * @package Lipe
 */

namespace Lipe\Traits;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;
use WordPressCS\WordPress\AbstractFunctionRestrictionsSniff;

/**
 * Helpers for working with objects.
 *
 * @author   Mat Lipe
 * @since    3.1.0
 *
 * @property File  $phpcsFile
 * @property array $tokens
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
		// @phpstan-ignore-next-line -- Some classes have this, some don't.
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
			if ( false === $next || T_OBJECT_OPERATOR !== $this->tokens[ $next ]['code'] ) {
				return false;
			}
		} elseif ( T_OBJECT_OPERATOR === $this->tokens[ $token ]['code'] ) {
			$variable = $this->phpcsFile->findPrevious( Tokens::$emptyTokens, $token + - 1, null, true, null, true );
			if ( false === $variable || T_VARIABLE !== $this->tokens[ $variable ]['code'] ) {
				return false;
			}
		} else {
			return false;
		}

		return $this->is_class_object( $variable );
	}


	/**
	 * Is this variable an instance of a class.
	 *
	 * @param int $token - Position of the variable token.
	 *
	 * @return bool
	 */
	protected function is_class_object( int $token ) : bool {
		if ( T_VARIABLE !== $this->tokens[ $token ]['code'] ) {
			return false;
		}

		$assignment = $this->get_variable_assignment( $token );
		if ( false === $assignment ) {
			return false;
		}

		$next = $this->phpcsFile->findNext( \array_merge( Tokens::$emptyTokens, [ T_EQUAL ] ), $assignment + 1, null, true, null, true );

		return false !== $next && T_NEW === $this->tokens[ $next ]['code'];
	}


	/**
	 * Get the properties assigned to an object.
	 *
	 * @param int $token - Position of the variable usage.
	 *
	 * @return array<string|int, int>
	 */
	protected function get_assigned_properties( int $token ) : array {
		$assignment = $this->get_variable_assignment( $token );
		if ( false === $assignment ) {
			return [];
		}

		$properties = [];
		$stackPtr = $assignment;
		while ( $stackPtr > 0 && $stackPtr < $token ) {
			$stackPtr = $this->phpcsFile->findNext( T_VARIABLE, $stackPtr + 1, null, false, $this->tokens[ $token ]['content'] );
			if ( false === $stackPtr ) {
				break;
			}
			$operator = $this->phpcsFile->findNext( Tokens::$emptyTokens, $stackPtr + 1, null, true, null, true );
			if ( T_OBJECT_OPERATOR !== $this->tokens[ $operator ]['code'] ) {
				continue;
			}

			$property = $this->phpcsFile->findNext( Tokens::$emptyTokens, $stackPtr + 2, null, true, null, true );
			if ( false === $property ) {
				continue;
			}

			$value = $this->phpcsFile->findNext( array_merge( [ T_EQUAL ], Tokens::$emptyTokens ), $property + 1, null, true, null, true );
			if ( false === $value ) {
				continue;
			}

			$properties[ $this->tokens[ $property ]['content'] ] = $value;
		}

		return $properties;
	}
}
