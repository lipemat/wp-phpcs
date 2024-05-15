<?php
/**
 * Lipe.WhiteSpace.OperatorSpacing
 *
 * @package Lipe
 */

namespace Lipe\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Standards\Squiz\Sniffs\WhiteSpace\OperatorSpacingSniff as PHPCS_Squiz_OperatorSpacingSniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Copied mostly from `\WordPressCS\WordPress\Sniffs\WhiteSpace\OperatorSpacingSniff`
 * - Except not requiring spacing in union types like `\Exception|\Throwable`.
 * - Not including JS in the supported tokenizers.
 *
 * @author Mat Lipe
 * @since  4.2.0
 */
class OperatorSpacingSniff extends PHPCS_Squiz_OperatorSpacingSniff {
	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var list<string>
	 * @phpstan-ignore-next-line -- Parent has incomplete type.
	 */
	public $supportedTokenizers = [ 'PHP' ];

	/**
	 * Allow newlines instead of spaces.
	 *
	 * N.B.: The upstream sniff defaults to `false`.
	 *
	 * @var boolean
	 */
	public $ignoreNewlines = true;


	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array<int|string>
	 */
	public function register(): array {
		$tokens = parent::register();
		// Allow union types in catch blocks to have no space between the pipes.
		unset( $tokens[ \T_BITWISE_OR ] );

		$tokens[ \T_BOOLEAN_NOT ] = \T_BOOLEAN_NOT;
		$tokens += Tokens::$booleanOperators;

		return $tokens;
	}
}
