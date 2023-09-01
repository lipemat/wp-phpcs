<?php
/**
 * Lipe.JS.InnerHTMLSniff
 *
 * @package wp-phpcs\Lipe
 */

namespace Lipe\Sniffs\JS;

use Lipe\Abstracts\AbstractEscapeOutputFunctions;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Looks for instances of .innerHMTL.
 */
class InnerHTMLSniff extends AbstractEscapeOutputFunctions {
	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param integer $stackPtr The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process_token( $stackPtr ): void {
		if ( 'innerHTML' !== $this->tokens[ $stackPtr ]['content'] ) {
			// Looking for .innerHTML only.
			return;
		}

		$functionToken = $this->phpcsFile->findNext( Tokens::$functionNameTokens, ( $stackPtr + 1 ) );

		if ( $this->isEscapeFunction( $functionToken ) ) {
			return;
		}

		$prevToken = $this->phpcsFile->findPrevious( Tokens::$emptyTokens, $stackPtr - 1, null, true, null, true );

		if ( T_OBJECT_OPERATOR !== $this->tokens[ $prevToken ]['code'] ) {
			return;
		}

		$nextToken = $this->phpcsFile->findNext( Tokens::$emptyTokens, $stackPtr + 1, null, true, null, true );

		if ( false === $nextToken || T_EQUAL !== $this->tokens[ $nextToken ]['code'] ) {
			// Not an assignment.
			return;
		}

		$nextToken = $this->phpcsFile->findNext( Tokens::$emptyTokens, $nextToken + 1, null, true, null, true );
		$foundVariable = false;

		while ( false !== $nextToken && T_SEMICOLON !== $this->tokens[ $nextToken ]['code'] ) {
			if ( T_STRING === $this->tokens[ $nextToken ]['code'] ) {
				$foundVariable = true;
				break;
			}

			$nextToken = $this->phpcsFile->findNext( Tokens::$emptyTokens, $nextToken + 1, null, true, null, true );
		}

		if ( true === $foundVariable ) {
			$message = 'Any HTML passed to `%s` gets executed. Consider using `.textContent` or make sure that used variables are properly escaped.';
			$data = [ $this->tokens[ $stackPtr ]['content'] ];
			$this->phpcsFile->addWarning( $message, $stackPtr, 'Found', $data );
		}
	}
}
