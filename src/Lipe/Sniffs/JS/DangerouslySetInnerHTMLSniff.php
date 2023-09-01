<?php
/**
 * Lipe.JS.DangerouslySetInnerHTML
 *
 * @package wp-phpcs\Lipe
 */

namespace Lipe\Sniffs\JS;

use Lipe\Traits\EscapeOutputFunctions;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Looks for instances of React's dangerouslySetInnerHTML.
 */
class DangerouslySetInnerHTMLSniff extends \WordPressVIPMinimum\Sniffs\JS\DangerouslySetInnerHTMLSniff {
	use EscapeOutputFunctions;

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param integer $stackPtr The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process_token( $stackPtr ) : void {
		if ( 'dangerouslySetInnerHTML' !== $this->tokens[ $stackPtr ]['content'] ) {
			// Looking for dangerouslySetInnerHTML only.
			return;
		}

		$functionToken = $this->phpcsFile->findNext( Tokens::$functionNameTokens, ( $stackPtr + 1 ) );
		if ( $this->isEscapeFunction( $functionToken ) ) {
			return;
		}

		parent::process_token( $stackPtr );
	}
}
