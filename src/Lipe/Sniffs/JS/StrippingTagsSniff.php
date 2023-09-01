<?php
/**
 * Lipe.JS.StrippingTags
 *
 * @package wp-phpcs\Lipe
 */

namespace Lipe\Sniffs\JS;

use Lipe\Abstracts\AbstractEscapeOutputFunctions;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Looks for incorrect way of stripping tags.
 */
class StrippingTagsSniff extends AbstractEscapeOutputFunctions {

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param integer $stackPtr The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process_token( $stackPtr ): void {
		if ( 'html' !== $this->tokens[ $stackPtr ]['content'] ) {
			// Looking for html() only.
			return;
		}

		$function = $this->phpcsFile->findNext( Tokens::$functionNameTokens, ( $stackPtr + 1 ) );

		if ( $this->isEscapeFunction( $function ) ) {
			return;
		}

		$nextToken = $this->phpcsFile->findNext( Tokens::$emptyTokens, $stackPtr + 1, null, true, null, true );

		if ( T_OPEN_PARENTHESIS !== $this->tokens[ $nextToken ]['code'] ) {
			// Not a function.
			return;
		}

		$afterFunctionCall = $this->phpcsFile->findNext( Tokens::$emptyTokens, $this->tokens[ $nextToken ]['parenthesis_closer'] + 1, null, true, null, true );

		if ( false === $afterFunctionCall || T_OBJECT_OPERATOR !== $this->tokens[ $afterFunctionCall ]['code'] ) {
			return;
		}

		$nextToken = $this->phpcsFile->findNext( Tokens::$emptyTokens, $afterFunctionCall + 1, null, true, null, true );

		if ( T_STRING === $this->tokens[ $nextToken ]['code'] && 'text' === $this->tokens[ $nextToken ]['content'] ) {
			$message = 'Vulnerable tag stripping approach detected.';
			$this->phpcsFile->addError( $message, $stackPtr, 'VulnerableTagStripping' );
		}
	}
}
