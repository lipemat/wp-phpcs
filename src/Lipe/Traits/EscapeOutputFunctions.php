<?php
/**
 * Trait used by scape sniff
 *
 * @package wp-phpcs\Lipe
 */

namespace Lipe\Traits;

use PHP_CodeSniffer\Util\Tokens;
use WordPressCS\WordPress\Helpers\RulesetPropertyHelper;

trait EscapeOutputFunctions {

	/**
	 * Custom list of functions, which escape values for output.
	 *
	 * @var list<string>
	 */
	public $customEscapingFunctions = [];

	/**
	 * List of functions, which escape values for output.
	 *
	 * @var array<string, bool>
	 */
	public $escapingFunctions = [
		'sanitize' => true,
	];

	/**
	 * Cache of previously added custom functions.
	 *
	 * Prevents having to do the same merges over and over.
	 *
	 * @var array<string, array<string>>
	 */
	private $addedCustomFunctions = [
		'escape' => [],
	];


	/**
	 * Detect if current position is an escape function
	 *
	 * @param int|false $functionToken token amount scape function.
	 *
	 * @return bool
	 */
	protected function isEscapeFunction( $functionToken ) : bool {
		if ( false === $functionToken ) {
			return false;
		}
		$this->mergeFunctionLists();

		$openParenthesis = $this->phpcsFile->findNext( Tokens::$emptyTokens, $functionToken + 1, null, true, null, true );

		if ( ! isset( $this->escapingFunctions[ $this->tokens[ $functionToken ]['content'] ], $this->tokens[ $openParenthesis ]['parenthesis_opener'], $this->tokens[ $openParenthesis ]['parenthesis_closer'] ) ) {
			return false;
		}

		return T_OPEN_PARENTHESIS === $this->tokens[ $openParenthesis ]['code'];
	}

	/**
	 * Merge custom functions provided via a custom ruleset with the defaults, if we haven't already.
	 *
	 * @return void
	 */
	protected function mergeFunctionLists() : void {
		if ( $this->customEscapingFunctions !== $this->addedCustomFunctions['escape'] ) {
			$customEscapeFunctions = RulesetPropertyHelper::merge_custom_array( $this->customEscapingFunctions, [], false );

			$this->escapingFunctions = RulesetPropertyHelper::merge_custom_array(
				$customEscapeFunctions,
				$this->escapingFunctions
			);

			$this->addedCustomFunctions['escape'] = $this->customEscapingFunctions;
		}
	}
}
