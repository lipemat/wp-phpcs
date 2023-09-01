<?php
/**
 * Lipe.JS.DangerouslySetInnerHTML
 *
 * @package wp-phpcs\Lipe
 */

namespace Lipe\Sniffs\JS;

use Lipe\Abstracts\AbstractEscapeOutputFunctions;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Looks for instances of React's dangerouslySetInnerHTML.
 */
class DangerouslySetInnerHTMLSniff extends AbstractEscapeOutputFunctions {

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param integer $stackPtr The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process_token( $stackPtr ): void {
		if ( 'dangerouslySetInnerHTML' !== $this->tokens[ $stackPtr ]['content'] ) {
			// Looking for dangerouslySetInnerHTML only.
			return;
		}

		$functionToken = $this->phpcsFile->findNext( Tokens::$functionNameTokens, ( $stackPtr + 1 ) );
		if ( $this->isEscapeFunction( $functionToken ) ) {
			return;
		}

		$nextToken = $this->phpcsFile->findNext( Tokens::$emptyTokens, $stackPtr + 1, null, true, null, true );

		if ( false === $nextToken || T_EQUAL !== $this->tokens[ $nextToken ]['code'] ) {
			// Not an assignment.
			return;
		}

		$nextNextToken = $this->phpcsFile->findNext( Tokens::$emptyTokens, $nextToken + 1, null, true, null, true );

		if ( T_OBJECT !== $this->tokens[ $nextNextToken ]['code'] ) {
			// Not react syntax.
			return;
		}

		$message = "Any HTML passed to `%s` gets executed. Please make sure it's properly escaped.";
		$data = [ $this->tokens[ $stackPtr ]['content'] ];
		$this->phpcsFile->addError( $message, $stackPtr, 'Found', $data );
	}
}
