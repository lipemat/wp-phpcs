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
use PHPCSUtils\Utils\TextStrings;
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
	public function is_targetted_token( $stackPtr ): bool {
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
	protected function is_object_assignment( int $token ): bool {
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

		return false !== $this->get_class_name( $variable );
	}


	/**
	 * Get the name of the class a variable is an instance of.
	 *
	 * @param int $token - Position of the variable token.
	 *
	 * @return false|string
	 */
	protected function get_class_name( int $token ) {
		$variable = $token;
		if ( T_VARIABLE !== $this->tokens[ $token ]['code'] ) {
			$variable = $this->phpcsFile->findPrevious( [ T_VARIABLE ], $token - 1, null, false, null, true );
		}
		if ( false === $variable ) {
			return false;
		}

		$assignment = $this->get_variable_assignment( $variable );
		if ( false === $assignment ) {
			return false;
		}

		$next = $this->phpcsFile->findNext( \array_merge( Tokens::$emptyTokens, [ T_EQUAL ] ), $assignment + 1, null, true, null, true );
		if ( false === $next ) {
			return false;
		}
		if ( T_NEW !== $this->tokens[ $next ]['code'] ) {
			return false;
		}
		$class = $this->phpcsFile->findNext( T_STRING, $next + 1 );
		return $this->tokens[ $class ]['content'];
	}



	/**
	 * Get the properties assigned to an object.
	 *
	 * @param int $token - Position of the variable usage.
	 *
	 * @return array<string|int, int>
	 */
	protected function get_assigned_properties( int $token ): array {
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

			$value = $this->phpcsFile->findNext( \array_merge( [ T_EQUAL ], Tokens::$emptyTokens ), $property + 1, null, true, null, true );
			if ( false === $value ) {
				continue;
			}

			$properties[ $this->tokens[ $property ]['content'] ] = $value;
		}

		return $properties;
	}


	/**
	 * Is this variable an instance of a class.
	 *
	 * @param string $name  - Name of the class.
	 * @param int    $token - Position of the variable token.
	 *
	 * @return bool
	 */
	protected function is_in_class( string $name, int $token ): bool {
		$class = $this->get_class_name( $token );
		return false !== $class && $name === $class;
	}


	/**
	 * Get the full value assigned to an object property from the token of
	 * the property.
	 *
	 * @param int $prop_token - Token of the property name.
	 *
	 * @return string|false
	 */
	protected function get_value_from_prop( int $prop_token ) {
		$prev = $this->phpcsFile->findPrevious( [ T_OBJECT_OPERATOR ], $prop_token - 1, null, false, null, true );
		// Not an object assignment.
		if ( false === $prev || $prev < $prop_token - 1 ) {
			return false;
		}
		$end = $this->phpcsFile->findEndOfStatement( $prop_token );
		$start = $this->phpcsFile->findNext( \array_merge( [ T_EQUAL ], Tokens::$emptyTokens ), $prop_token + 1, null, true, null, true );
		if ( false === $start ) {
			return false;
		}
		$value = '';
		for ( $i = $start; $i < $end; $i ++ ) {
			$value .= $this->tokens[ $i ]['content'];
		}
		return TextStrings::stripQuotes( $value );
	}
}
