<?php
/**
 * Lipe.Performance.SuppressFilters
 *
 * @package Lipe
 */

namespace Lipe\Sniffs\Performance;

use Lipe\Traits\ArrayHelpers;
use Lipe\Traits\ObjectHelpers;
use Lipe\Traits\VariableHelpers;
use PHP_CodeSniffer\Util\Tokens;
use VariableAnalysis\Lib\Helpers;
use WordPressCS\WordPress\AbstractFunctionRestrictionsSniff;

/**
 * Verify that suppress_filters is not used.
 *
 * - get_posts() has a suppress_filters which must be set to false.
 *
 * @author Mat Lipe
 * @since  3.1.0
 */
class SuppressFiltersSniff extends AbstractFunctionRestrictionsSniff {
	use ArrayHelpers;
	use ObjectHelpers;
	use VariableHelpers;

	/**
	 * Groups of functions to restrict.
	 *
	 * @return array
	 */
	public function getGroups() : array {
		return [
			'get_posts' => [
				'type'      => 'warning',
				'message'   => '%s() is uncached unless the "suppress_filters" parameter is set to false. More Info: https://docs.wpvip.com/technical-references/caching/uncached-functions/.',
				'functions' => [
					'get_posts',
					'wp_get_recent_posts',
					'get_children',
				],
			],
		];
	}


	/**
	 * Process a matched token.
	 *
	 * @since 0.11.0 Split out from the `process()` method.
	 *
	 * @param int    $stackPtr        The position of the current token in the stack.
	 * @param string $group_name      The name of the group which was matched.
	 * @param string $matched_content The token content (function name) which was matched.
	 *
	 * @return int|void Integer stack pointer to skip forward or void to continue
	 *                  normal file processing.
	 */
	public function process_matched_token( $stackPtr, $group_name, $matched_content ) {
		$array_open = $this->phpcsFile->findNext( \array_merge( Tokens::$emptyTokens, [ \T_OPEN_PARENTHESIS ] ), $stackPtr + 1, null, true );

		if ( \in_array( $this->tokens[ $array_open ]['code'], [ T_ARRAY_HINT, T_OPEN_SHORT_ARRAY ], true ) ) {
			$array_bounds = $this->find_array_open_close( $array_open );
			$elements = $this->get_array_indices( $array_bounds['opener'], $array_bounds['closer'] );
			$compare_element = $this->find_key_in_array( $elements, 'suppress_filters' );

			// No suppress_filters key found.
			if ( empty( $compare_element ) ) {
				$this->addMessage(
					$this->groups[ $group_name ]['message'],
					$stackPtr,
					( 'error' === $this->groups[ $group_name ]['type'] ),
					$this->string_to_errorcode( $matched_content ),
					[ $matched_content ]
				);
				return;
			}

			// suppress_filters key found, but not set to false.
			if ( 'false' !== $this->tokens[ $compare_element['value_start'] ]['content'] ) {
				$this->addMessage(
					'Setting "suppress_filters" parameter to true in %s() is prohibited. More Info: https://docs.wpvip.com/technical-references/caching/uncached-functions/.',
					$stackPtr,
					true,
					$this->string_to_errorcode( $matched_content ),
					[ $matched_content ]
				);
			}

			return;
		}

		if ( Helpers::isTokenInsideFunctionCallArgument( $this->phpcsFile, $array_open ) ) {
			$variable = $array_open;
			// Unable to determine if suppress_filters is set to false.
			if ( $variable < $stackPtr ) {
				$this->addMessage(
					$this->groups[ $group_name ]['message'],
					$stackPtr,
					( 'error' === $this->groups[ $group_name ]['type'] ),
					$this->string_to_errorcode( $matched_content ),
					[ $matched_content ]
				);
				return;
			}

			if ( $this->is_variable_an_array( $variable ) ) {
				$assigned = $this->get_assigned_keys_from_variable( $variable );
			} else {
				$assigned = $this->get_assigned_properties( $variable );
			}

			// Not assigned to anything.
			if ( ! isset( $assigned['suppress_filters'] ) ) {
				$this->addMessage(
					$this->groups[ $group_name ]['message'],
					$stackPtr,
					( 'error' === $this->groups[ $group_name ]['type'] ),
					$this->string_to_errorcode( $matched_content ),
					[ $matched_content ]
				);
				return;
			}

			// Assigned to something other than false.
			if ( 'false' !== $this->tokens[ $assigned['suppress_filters'] ]['content'] ) {
				$this->addMessage(
					'Setting "suppress_filters" parameter to true in %s() is prohibited. More Info: https://docs.wpvip.com/technical-references/caching/uncached-functions/.',
					$stackPtr,
					true,
					$this->string_to_errorcode( $matched_content ),
					[ $matched_content ]
				);
				return;
			}
		}

		// Used without arguments.
		if ( \T_CLOSE_PARENTHESIS === $this->tokens[ $array_open ]['code'] ) {
			$this->addMessage(
				$this->groups[ $group_name ]['message'],
				$stackPtr,
				( 'error' === $this->groups[ $group_name ]['type'] ),
				$this->string_to_errorcode( $matched_content ),
				[ $matched_content ]
			);
		}
	}
}
