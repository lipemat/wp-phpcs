<?php
/**
 * Helpers for working with variables.
 *
 * @since   3.1.0
 * @package Lipe
 */

namespace Lipe\Traits;

use PHP_CodeSniffer\Util\Tokens;
use PHPCSUtils\Utils\TextStrings;
use VariableAnalysis\Lib\Helpers;

/**
 * Helpers for working with variables.
 *
 * @author Mat Lipe
 * @since  3.1.0
 */
trait VariableHelpers {
	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var string[]
	 */
	public $supportedTokenizers = [ 'PHP' ];

	/**
	 * Class property token types.
	 *
	 * - Access modifiers.
	 * - Variable types. (T_STRING) is the only thing available.
	 *
	 * @var list<int|string>
	 */
	public array $property_tokens = [
		T_PRIVATE,
		T_PROTECTED,
		T_PUBLIC,
		T_READONLY,
		T_STATIC,
		T_STRING,
		T_VAR,
	];

	/**
	 * Cached empty tokens + T_EQUAL set, built once per process.
	 *
	 * @var array<int|string, int|string>
	 */
	private static array $empty_plus_equal;

	/**
	 * Per-file memo for {@see VariableHelpers::get_variable_assignment()}.
	 *
	 * Keyed by `spl_object_id( $phpcsFile ) . ':' . $token`.
	 *
	 * @var array<string, int|false>
	 */
	private array $variable_assignment_cache = [];

	/**
	 * `spl_object_id` of the file the {@see $variable_assignment_cache} was built for.
	 *
	 * @var int|null
	 */
	private ?int $variable_assignment_cache_file = null;


	/**
	 * Lazily build (once) the merged "empty tokens + T_EQUAL" set used by
	 * several helpers. Avoids `array_merge( Tokens::$emptyTokens, [ T_EQUAL ] )`
	 * on every call.
	 *
	 * @return array<int|string, int|string>
	 */
	protected function empty_plus_equal_tokens(): array {
		if ( ! isset( self::$empty_plus_equal ) ) {
			self::$empty_plus_equal = \array_merge( Tokens::$emptyTokens, [ T_EQUAL ] );
		}
		return self::$empty_plus_equal;
	}


	/**
	 * Get the position of a variable assignment based
	 * on the variable usage.
	 *
	 * @param int $token - Position of the variable usage.
	 *
	 * @return int|false
	 */
	protected function get_variable_assignment( int $token ) {
		$file_id = \spl_object_id( $this->phpcsFile );
		if ( $this->variable_assignment_cache_file !== $file_id ) {
			$this->variable_assignment_cache = [];
			$this->variable_assignment_cache_file = $file_id;
		}
		$cache_key = $token . ':' . $this->tokens[ $token ]['content'];
		if ( \array_key_exists( $cache_key, $this->variable_assignment_cache ) ) {
			return $this->variable_assignment_cache[ $cache_key ];
		}

		$property = $this->get_class_property( $token );
		if ( false !== $property ) {
			$stackPtr = $property;
			$content = $this->tokens[ $property ]['content'];
		} else {
			$content = $this->tokens[ $token ]['content'];
			$stackPtr = $token;
		}

		// This is the assignment statement.
		$next = $this->phpcsFile->findNext( Tokens::$emptyTokens, $stackPtr + 1, null, true, null, true );
		if ( false !== $next && T_EQUAL === $this->tokens[ $next ]['code'] ) {
			$this->variable_assignment_cache[ $cache_key ] = $stackPtr;
			return $stackPtr;
		}

		$scope_start = $this->get_enclosing_scope_start( $token );

		while ( $stackPtr > 0 ) {
			$stackPtr = $this->phpcsFile->findPrevious( T_VARIABLE, $stackPtr - 1, $scope_start, false, $content );

			if ( false === $stackPtr ) {
				$this->variable_assignment_cache[ $cache_key ] = false;
				return false;
			}

			$next = $this->phpcsFile->findNext( Tokens::$emptyTokens, $stackPtr + 1, null, true, null, true );
			if ( false !== $next && T_EQUAL === $this->tokens[ $next ]['code'] ) {
				$this->variable_assignment_cache[ $cache_key ] = $stackPtr;
				return $stackPtr;
			}

			if ( Helpers::isTokenFunctionParameter( $this->phpcsFile, $stackPtr ) ) {
				$this->variable_assignment_cache[ $cache_key ] = $stackPtr;
				return $stackPtr;
			}
		}

		$this->variable_assignment_cache[ $cache_key ] = false;
		return false;
	}


	/**
	 * Find the start of the enclosing function/closure/class for a token, or
	 * null to indicate "no upper bound" (file scope).
	 *
	 * Used to bound backwards `findPrevious` walks so they don't traverse the
	 * entire file looking for variable assignments that can't legally exist
	 * outside the current scope.
	 *
	 * @param int $token - Position of the token.
	 *
	 * @return int|null - The opener position, or null when no enclosing scope.
	 */
	protected function get_enclosing_scope_start( int $token ): ?int {
		if ( ! isset( $this->tokens[ $token ]['conditions'] ) ) {
			return null;
		}
		$conditions = $this->tokens[ $token ]['conditions'];
		if ( [] === $conditions ) {
			return null;
		}
		foreach ( \array_reverse( $conditions, true ) as $ptr => $code ) {
			if ( T_FUNCTION === $code || T_CLOSURE === $code || T_FN === $code || T_CLASS === $code || T_ANON_CLASS === $code || T_TRAIT === $code ) {
				return $ptr;
			}
		}
		return null;
	}


	/**
	 * Get the static value of a variable.
	 *
	 * - If the token is already a static value return it.
	 * - If the token is a variable, find the assignment and return the static value.
	 * - If the variable is not assigned a static value return false.
	 *
	 * @param int $token - Position of the variable usage.
	 *
	 * @return null|string
	 */
	protected function get_static_value_from_variable( int $token ): ?string {
		if ( $this->is_scalar( $token ) ) {
			return TextStrings::stripQuotes( $this->tokens[ $token ]['content'] );
		}
		if ( T_VARIABLE !== $this->tokens[ $token ]['code'] ) {
			return null;
		}
		$assignment = $this->get_variable_assignment( $token );
		if ( false === $assignment ) {
			return null;
		}

		$next = $this->phpcsFile->findNext( $this->empty_plus_equal_tokens(), $assignment + 1, null, true, null, true );
		if ( false !== $next && T_VARIABLE === $this->tokens[ $next ]['code'] ) {
			return $this->get_static_value_from_variable( $next );
		}
		if ( false === $next || ! $this->is_scalar( $next ) ) {
			return null;
		}
		return TextStrings::stripQuotes( $this->tokens[ $next ]['content'] );
	}


	/**
	 * Get the position of a class property declaration based on
	 * the usage of $this.
	 *
	 * @param int $token - Position of the $this token.
	 *
	 * @return false|int
	 */
	protected function get_class_property( int $token ) {
		if ( '$this' !== $this->tokens[ $token ]['content'] ) {
			return false;
		}
		$name = $this->phpcsFile->findNext( [ T_STRING ], $token + 1, null, false, null, true );
		$class_start = $this->get_start_of_class( $token );
		if ( false === $name || false === $class_start ) {
			return false;
		}
		$content = '$' . $this->tokens[ $name ]['content'];
		$stackPtr = $token;

		while ( $stackPtr > 0 ) {
			$stackPtr = $this->phpcsFile->findPrevious( T_VARIABLE, $stackPtr - 1, $class_start, false, $content );
			if ( false === $stackPtr ) {
				return false;
			}

			$next = $this->phpcsFile->findNext( Tokens::$emptyTokens, $stackPtr + 1, null, true, null, true );
			if ( false !== $next && T_EQUAL === $this->tokens[ $next ]['code'] ) {
				try {
					$this->phpcsFile->getMemberProperties( $stackPtr );
				} catch ( \RuntimeException $e ) {
					return false;
				}
				return $stackPtr;
			}
		}

		return false;
	}


	/**
	 * Get the start of a class based on the usage of $this.
	 *
	 * @param int $this_variable - Position of the $this token.
	 *
	 * @return false|int
	 */
	protected function get_start_of_class( int $this_variable ) {
		if ( '$this' !== $this->tokens[ $this_variable ]['content'] ) {
			return false;
		}

		$conditions = \array_keys( $this->tokens[ $this_variable ]['conditions'] );
		$ptr = (int) \reset( $conditions );
		if ( ! isset( $this->tokens[ $ptr ] ) || ! in_array( $this->tokens[ $ptr ]['code'], [ T_CLASS, T_ANON_CLASS, T_TRAIT ], true ) ) {
			return false;
		}
		return $ptr;
	}


	/**
	 * Is this value a constant string, number, true, or false?
	 *
	 * @param int|false $token - Position of the token.
	 *
	 * @return bool
	 */
	protected function is_scalar( $token ): bool {
		if ( false === $token ) {
			return false;
		}
		return \in_array( $this->tokens[ $token ]['code'], [ T_TRUE, T_CONSTANT_ENCAPSED_STRING, T_FALSE, T_LNUMBER, T_DNUMBER ], true );
	}
}
