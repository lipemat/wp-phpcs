<?php
/**
 * Helpers for working with arrays.
 *
 * @since   3.1.0
 * @package Lipe
 */

namespace Lipe\Traits;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;
use PHPCSUtils\Utils\Arrays;
use PHPCSUtils\Utils\TextStrings;
use VariableAnalysis\Lib\Helpers;

/**
 * Helpers for working with arrays.
 *
 * @phpstan-type ArrayElement array{index_start?: int, index_end?: int|false, arrow?: int, value_start: int|false}
 *
 * @author Mat Lipe
 * @since  3.1.0
 */
trait ArrayHelpers {
	use VariableHelpers;

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var string[]
	 */
	public $supportedTokenizers = [ 'PHP' ];

	/**
	 * Tokens that indicate an array.
	 *
	 * @var array<int|string>
	 */
	public static $array_tokens = [
		T_ARRAY,
		T_OPEN_SHORT_ARRAY,
		T_OPEN_SQUARE_BRACKET,
		T_ARRAY_HINT,
	];


	/**
	 * Is a variable an array?
	 *
	 * Finds the position of the variable assignment and then
	 * checks if the next token is an array.
	 *
	 * @param int $token - Position of the variable usage.
	 *
	 * @return bool
	 */
	protected function is_variable_an_array( int $token ): bool {
		if ( false !== $this->get_array_opener( $token ) ) {
			return true;
		}
		$assignment = $this->get_variable_assignment( $token );
		if ( false === $assignment ) {
			return false;
		}
		$next = $this->phpcsFile->findNext( \array_merge( Tokens::$emptyTokens, [ \T_EQUAL ] ), $assignment + 1, null, true, null, true );
		if ( false === $next ) {
			return false;
		}

		if ( T_VARIABLE === $this->tokens[ $next ]['code'] || Helpers::isTokenFunctionParameter( $this->phpcsFile, $next ) ) {
			$assignment = $this->phpcsFile->findNext( T_VARIABLE, $assignment + 1, null, false, $this->tokens[ $token ]['content'] );
			if ( false === $assignment ) {
				return false;
			}
			$bracket = $this->phpcsFile->findNext( T_OPEN_SQUARE_BRACKET, $assignment + 1, null, false, null, true );
			if ( false !== $bracket && $bracket < $token ) {
				return true;
			}

			// See if we can determine if the assigned variable is an array.
			if ( T_VARIABLE === $this->tokens[ $next ]['code'] ) {
				return $this->is_variable_an_array( $next );
			}
		}

		return false;
	}


	/**
	 * Get the keys assigned to an array based on the variable usage.
	 *
	 * @param int $token - Position of the variable usage.
	 *
	 * @return array<string, int>
	 */
	protected function get_assigned_keys_from_variable( int $token ): array {
		$assignment = $this->get_variable_assignment( $token );
		if ( false === $assignment ) {
			return [];
		}

		$values = [];
		$array_open = $this->get_array_opener( $assignment );
		if ( false !== $array_open && \in_array( $this->tokens[ $array_open ]['code'], static::$array_tokens, true ) ) {
			$values = $this->get_assigned_keys( $array_open );
		}

		return $this->get_array_access_values( $token, $values );
	}


	/**
	 * Get value tokens from array access assignment using square brackets.
	 *
	 * @param int                $token  - Position of the variable usage.
	 * @param array<string, int> $values - Array of values to add to.
	 *
	 * @return array<string, int>
	 */
	protected function get_array_access_values( int $token, array $values ): array {
		$assignment = $this->get_variable_assignment( $token );
		if ( false === $assignment || '$this' === $this->tokens[ $token ]['content'] ) {
			return $values;
		}

		while ( $assignment < $token ) {
			$assignment = $this->phpcsFile->findNext( T_VARIABLE, $assignment + 1, null, false, $this->tokens[ $token ]['content'] );
			if ( false === $assignment ) {
				break;
			}

			$bracket = $this->phpcsFile->findNext( T_OPEN_SQUARE_BRACKET, $assignment + 1, null, false, null, true );
			if ( false === $bracket ) {
				break;
			}

			$key = $this->phpcsFile->findNext( Tokens::$emptyTokens, $bracket + 1, null, true );
			if ( false !== $key && T_CONSTANT_ENCAPSED_STRING === $this->tokens[ $key ]['code'] ) {
				$index = TextStrings::stripQuotes( $this->tokens[ $key ]['content'] );
				$value = $this->phpcsFile->findNext( array_merge( Tokens::$emptyTokens, [ T_EQUAL, T_CLOSE_SQUARE_BRACKET ] ), $key + 1, null, true );
				if ( false !== $value ) {
					$values[ $index ] = $value;
				}
			}
		}

		return $values;
	}


	/**
	 * Get the keys and position of their values assigned to an array.
	 *
	 * @param int $array_open - Position of the array opener.
	 *
	 * @return array<string, int>
	 */
	protected function get_assigned_keys( int $array_open ): array {
		$array_bounds = Arrays::getOpenClose( $this->phpcsFile, $array_open );
		if ( false === $array_bounds || ! isset( $array_bounds['opener'], $array_bounds['closer'] ) ) {
			return [];
		}
		$elements = $this->get_array_indices( $array_bounds['opener'], $array_bounds['closer'] );

		$properties = [];
		foreach ( $elements as $element ) {
			if ( ! isset( $element['index_start'] ) ) {
				continue;
			}

			// Ensure the index is a static string first.
			$start = $element['index_start'];
			if ( T_CONSTANT_ENCAPSED_STRING !== $this->tokens[ $start ]['code'] ) {
				// Dynamic key.
				continue;
			}

			$maybe_index_end = $this->phpcsFile->findNext( Tokens::$emptyTokens, $start + 1, null, true );
			if ( T_DOUBLE_ARROW !== $this->tokens[ $maybe_index_end ]['code'] ) {
				// Dynamic key, maybe? This is probably not valid syntax.
				continue;
			}

			$index = TextStrings::stripQuotes( $this->tokens[ $start ]['content'] );
			if ( false !== $element['value_start'] ) {
				$properties[ $index ] = $element['value_start'];
			}
		}

		return $properties;
	}


	/**
	 * Get the token for the value in an array by key.
	 *
	 * Returns null if the key is not found.
	 *
	 * @param int    $token - Position of the variable usage.
	 * @param string $key   - Key to search for.
	 *
	 * @return int|false
	 */
	protected function find_key_in_array( int $token, string $key ) {
		$array_open = $this->get_array_opener( $token );
		if ( false === $array_open ) {
			return false;
		}
		$array_bounds = Arrays::getOpenClose( $this->phpcsFile, $array_open );
		if ( false === $array_bounds || ! isset( $array_bounds['opener'], $array_bounds['closer'] ) ) {
			return false;
		}
		$elements = $this->get_array_indices( $array_bounds['opener'], $array_bounds['closer'] );
		$element = $this->find_key_in_array_elements( $elements, $key );
		if ( null === $element ) {
			return false;
		}
		return $element['value_start'];
	}


	/**
	 * Find a given key in an array.
	 *
	 * Searches a list of elements for a given (static) index.
	 *
	 * @phpstan-param ArrayElement[] $elements
	 *
	 * @param array  $elements  Elements from the array (from get_array_indices()).
	 * @param string $array_key Key to find in the array.
	 *
	 * @phpstan-return ArrayElement|null
	 *
	 * @return array|null -Static value if available, null otherwise.
	 */
	protected function find_key_in_array_elements( array $elements, string $array_key ) {
		foreach ( $elements as $element ) {
			if ( ! isset( $element['index_start'] ) ) {
				// Numeric item, skip.
				continue;
			}

			// Ensure the index is a static string first.
			$start = $element['index_start'];
			if ( T_CONSTANT_ENCAPSED_STRING !== $this->tokens[ $start ]['code'] ) {
				// Dynamic key.
				continue;
			}

			$maybe_index_end = $this->phpcsFile->findNext( Tokens::$emptyTokens, $start + 1, null, true );
			if ( T_DOUBLE_ARROW !== $this->tokens[ $maybe_index_end ]['code'] ) {
				// Dynamic key, maybe? This is probably not valid syntax.
				continue;
			}

			$index = TextStrings::stripQuotes( $this->tokens[ $start ]['content'] );
			if ( $index !== $array_key ) {
				// Not the item we want, skip.
				continue;
			}

			return $element;
		}

		return null;
	}


	/**
	 * Get array indices information.
	 *
	 * @internal From phpcs' AbstractArraySniff::get_array_indices
	 *
	 * @param integer $array_start Position in the stack of the array opener.
	 * @param integer $array_end   Position in the stack of the array closer.
	 *
	 * @return ArrayElement[]
	 */
	protected function get_array_indices( int $array_start, int $array_end ): array {
		$indices = [];

		$current = $array_start;
		$next = $this->phpcsFile->findNext( Tokens::$emptyTokens, ( $current + 1 ), $array_end, true );
		while ( false !== $next ) {
			$end = $this->get_next( $this->phpcsFile, $next, $array_end );

			if ( T_DOUBLE_ARROW === $this->tokens[ $end ]['code'] ) {
				$indexEnd = $this->phpcsFile->findPrevious( T_WHITESPACE, $end - 1, null, true );
				$value_start = $this->phpcsFile->findNext( Tokens::$emptyTokens, $end + 1, null, true );

				$indices[] = [
					'index_start' => $next,
					'index_end'   => $indexEnd,
					'arrow'       => $end,
					'value_start' => $value_start,
				];
			} else {
				$value_start = $next;
				$indices[] = [
					'value_start' => $value_start,
				];
			}
			if ( false === $value_start ) {
				break;
			}
			$current = $this->get_next( $this->phpcsFile, $value_start, $array_end );
			$next = $this->phpcsFile->findNext( Tokens::$emptyTokens, ( $current + 1 ), $array_end, true );
		}

		return $indices;
	}


	/**
	 * Find next separator in an array - either: comma or double arrow.
	 *
	 * @internal From phpcs' AbstractArraySniff::getNext
	 *
	 * @param File $phpcsFile The current file being checked.
	 * @param int  $ptr       The position of current token.
	 * @param int  $arrayEnd  The token ends the array definition.
	 *
	 * @return int
	 */
	protected function get_next( File $phpcsFile, int $ptr, int $arrayEnd ): int {
		$tokens = $phpcsFile->getTokens();

		while ( $ptr < $arrayEnd ) {
			if ( true === isset( $tokens[ $ptr ]['scope_closer'] ) ) {
				$ptr = $tokens[ $ptr ]['scope_closer'];
			} elseif ( true === isset( $tokens[ $ptr ]['parenthesis_closer'] ) ) {
				$ptr = $tokens[ $ptr ]['parenthesis_closer'];
			} elseif ( true === isset( $tokens[ $ptr ]['bracket_closer'] ) ) {
				$ptr = $tokens[ $ptr ]['bracket_closer'];
			}

			if ( T_COMMA === $tokens[ $ptr ]['code'] || T_DOUBLE_ARROW === $tokens[ $ptr ]['code'] ) {
				return $ptr;
			}

			++ $ptr;
		}

		return $ptr;
	}


	/**
	 * Get a static value from an array.
	 *
	 * @param int $value_start Position in the stack of the value.
	 *
	 * @return string|null Static value if available, null otherwise.
	 */
	protected function get_static_value_for_element( int $value_start ) {
		if ( ! $this->is_scalar( $value_start ) ) {
			if ( T_VARIABLE === $this->tokens[ $value_start ]['code'] ) {
				$value = $this->get_static_value_from_variable( $value_start );
				if ( null !== $value ) {
					return $value;
				}
			}
			return '__dynamic';
		}

		$maybe_value_end = $this->phpcsFile->findNext( Tokens::$emptyTokens, $value_start + 1, null, true );
		$expected_next = [
			T_CLOSE_PARENTHESIS,
			T_CLOSE_SHORT_ARRAY,
			T_COMMA,
		];
		if ( ! in_array( $this->tokens[ $maybe_value_end ]['code'], $expected_next, true ) ) {
			// Dynamic value.
			return '__dynamic';
		}

		return TextStrings::stripQuotes( $this->tokens[ $value_start ]['content'] );
	}


	/**
	 * Based on either the array opener or the variable assignment, find the array opener.
	 *
	 * @param int $token Position in the stack of the array opener or variable assignment.
	 *
	 * @return int|false
	 */
	protected function get_array_opener( int $token ) {
		$array_open = $token;
		if ( T_VARIABLE === $this->tokens[ $token ]['code'] ) {
			$assignment = $this->get_variable_assignment( $token );
			if ( false === $assignment ) {
				return false;
			}
			$array_open = $this->phpcsFile->findNext( \array_merge( Tokens::$emptyTokens, [ \T_EQUAL ] ), $assignment + 1, null, true, null, true );
			if ( false !== $array_open && T_ARRAY_CAST === $this->tokens[ $array_open ]['code'] ) {
				$array_open = $this->phpcsFile->findNext( static::$array_tokens, $array_open + 1, null, false, null, true );
			}
		}

		if ( false === $array_open ) {
			return false;
		}

		if ( \in_array( $this->tokens[ $array_open ]['code'], static::$array_tokens, true ) ) {
			return $array_open;
		}

		if ( T_VARIABLE === $this->tokens[ $array_open ]['code'] ) {
			return $this->get_array_opener( $array_open );
		}

		return false;
	}
}
