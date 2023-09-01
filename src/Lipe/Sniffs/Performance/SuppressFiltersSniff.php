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
use PHPCSUtils\Utils\MessageHelper;
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
	 * @return array<string, array<mixed>>
	 */
	public function getGroups(): array {
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
	 * @return void Integer stack pointer to skip forward or void to continue
	 *                  normal file processing.
	 */
	public function process_matched_token( $stackPtr, $group_name, $matched_content ): void {
		$array_open = $this->phpcsFile->findNext( \array_merge( Tokens::$emptyTokens, [ \T_OPEN_PARENTHESIS ] ), $stackPtr + 1, null, true );

		if ( false === $array_open ) {
			return;
		}

		if ( \in_array( $this->tokens[ $array_open ]['code'], static::$array_tokens, true ) ) {
			$compare_element = $this->find_key_in_array( $array_open, 'suppress_filters' );

			// No suppress_filters key found.
			if ( false === $compare_element ) {
				MessageHelper::addMessage(
					$this->phpcsFile,
					$this->groups[ $group_name ]['message'],
					$stackPtr,
					( 'error' === $this->groups[ $group_name ]['type'] ),
					MessageHelper::stringToErrorcode( $matched_content ),
					[ $matched_content ]
				);
				return;
			}

			// suppress_filters key found, but not set to false.
			if ( 'false' !== $this->tokens[ $compare_element ]['content'] ) {
				MessageHelper::addMessage(
					$this->phpcsFile,
					'Setting "suppress_filters" parameter to true in %s() is prohibited. More Info: https://docs.wpvip.com/technical-references/caching/uncached-functions/.',
					$stackPtr,
					true,
					MessageHelper::stringToErrorcode( $matched_content ),
					[ $matched_content ]
				);
			}

			return;
		}

		if ( Helpers::isTokenInsideFunctionCallArgument( $this->phpcsFile, $array_open ) ) {
			$variable = $array_open;
			if ( $this->is_variable_an_array( $variable ) ) {
				$assigned = $this->get_assigned_keys_from_variable( $variable );
			} else {
				$assigned = $this->get_assigned_properties( $variable );
				// Special case for any fluent interfaces' `get_light_args` methods.
				if ( ! isset( $assigned['suppress_filters'] ) && T_OBJECT_OPERATOR === $this->tokens[ $variable + 1 ]['code'] ) {
					$call = $this->phpcsFile->findNext( \T_STRING, ( $variable + 2 ) );
					if ( false !== $call && 'get_light_args' === $this->tokens[ $call ]['content'] ) {
						return;
					}
				}
			}

			// Not assigned to anything.
			if ( ! isset( $assigned['suppress_filters'] ) ) {
				MessageHelper::addMessage(
					$this->phpcsFile,
					$this->groups[ $group_name ]['message'],
					$stackPtr,
					( 'error' === $this->groups[ $group_name ]['type'] ),
					MessageHelper::stringToErrorcode( $matched_content ),
					[ $matched_content ]
				);
				return;
			}

			// Assigned to something other than false.
			if ( 'false' !== $this->tokens[ $assigned['suppress_filters'] ]['content'] ) {
				MessageHelper::addMessage(
					$this->phpcsFile,
					'Setting "suppress_filters" parameter to true in %s() is prohibited. More Info: https://docs.wpvip.com/technical-references/caching/uncached-functions/.',
					$stackPtr,
					true,
					MessageHelper::stringToErrorcode( $matched_content ),
					[ $matched_content ]
				);
				return;
			}
		}

		// Used without arguments.
		if ( \T_CLOSE_PARENTHESIS === $this->tokens[ $array_open ]['code'] ) {
			MessageHelper::addMessage(
				$this->phpcsFile,
				$this->groups[ $group_name ]['message'],
				$stackPtr,
				( 'error' === $this->groups[ $group_name ]['type'] ),
				MessageHelper::stringToErrorcode( $matched_content ),
				[ $matched_content ]
			);
		}
	}
}
