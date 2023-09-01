<?php
/**
 * Lipe.JS.InnerHTMLSniff
 *
 * @package wp-phpcs\Lipe
 */

namespace Lipe\Sniffs\JS;

use Lipe\Traits\EscapeOutputFunctions;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Looks for instances of .innerHMTL.
 */
class InnerHTMLSniff extends \WordPressVIPMinimum\Sniffs\JS\InnerHTMLSniff {
	use EscapeOutputFunctions;

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param integer $stackPtr The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process_token( $stackPtr ) : void {
		if ( 'innerHTML' !== $this->tokens[ $stackPtr ]['content'] ) {
			// Looking for .innerHTML only.
			return;
		}

		$functionToken = $this->phpcsFile->findNext( Tokens::$functionNameTokens, ( $stackPtr + 1 ) );

		if ( $this->isEscapeFunction( $functionToken ) ) {
			return;
		}

		parent::process_token( $stackPtr );
	}

}
