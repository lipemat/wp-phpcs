<?php
declare( strict_types=1 );

namespace Lipe\Traits;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Helpers for working with arrays.
 *
 * @author Mat Lipe
 * @since  3.10.0
 */
trait ArrayHelpers {
	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var string[]
	 */
	public $supportedTokenizers = [ 'PHP' ];


	/**
	 * Find a given key in an array.
	 *
	 * Searches a list of elements for a given (static) index.
	 *
	 * @param array<array> $elements  Elements from the array (from get_array_indices()).
	 * @param string       $array_key Key to find in the array.
	 *
	 * @return array|null Static value if available, null otherwise.
	 */
	protected function find_key_in_array( array $elements, string $array_key ) {
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

			$index = $this->strip_quotes( $this->tokens[ $start ]['content'] );
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
	 * @return array
	 */
	protected function get_array_indices( int $array_start, int $array_end ) : array {
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
	protected function get_next( File $phpcsFile, int $ptr, int $arrayEnd ) : int {
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
	 * @param array $element Elements from the array (from get_array_indices()).
	 *
	 * @return string|null Static value if available, null otherwise.
	 */
	protected function get_static_value_for_element( array $element ) {
		// Got the compare, grab the value.
		$value_start = $element['value_start'];
		if ( T_CONSTANT_ENCAPSED_STRING !== $this->tokens[ $value_start ]['code'] ) {
			// Dynamic value.
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

		return $this->strip_quotes( $this->tokens[ $value_start ]['content'] );
	}
}
