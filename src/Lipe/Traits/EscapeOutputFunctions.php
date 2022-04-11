<?php
/**
 * Trait used by scape sniff
 *
 * @package wp-phpcs\Lipe
 */

namespace Lipe\Traits;

use PHP_CodeSniffer\Util\Tokens;

trait EscapeOutputFunctions {

	/**
	 * Custom list of functions, which escape values for output.
	 *
	 * @var string[]
	 */
	public $customEscapingFunctions = [];

	/**
	 * Cache of previously added custom functions.
	 *
	 * Prevents having to do the same merges over and over.
	 *
	 * @var array
	 */
	private $addedCustomFunctions = [
		'escape' => [],
	];


	/**
	 * Detect if current position is an escape function
	 *
	 * @param int $functionToken token amount scape function.
	 *
	 * @return bool
	 */
	protected function isEscapeFunction( $functionToken ) {
		$this->mergeFunctionLists();

		$openParenthesis = $this->phpcsFile->findNext( Tokens::$emptyTokens, $functionToken + 1, null, true, null, true );

		return isset( $this->escapingFunctions[ $this->tokens[ $functionToken ]['content'] ], $this->tokens[ $openParenthesis ]['parenthesis_opener'], $this->tokens[ $openParenthesis ]['parenthesis_closer'] ) && T_OPEN_PARENTHESIS === $this->tokens[ $openParenthesis ]['code'];
	}

	/**
	 * Merge custom functions provided via a custom ruleset with the defaults, if we haven't already.
	 *
	 * @return void
	 */
	protected function mergeFunctionLists() {
		if ( $this->customEscapingFunctions !== $this->addedCustomFunctions['escape'] ) {
			$customEscapeFunctions = static::merge_custom_array( $this->customEscapingFunctions, [], false );

			$this->escapingFunctions = static::merge_custom_array(
				$customEscapeFunctions,
				[ 'sanitize' => true ]
			);

			$this->addedCustomFunctions['escape'] = $this->customEscapingFunctions;
		}
	}
}
