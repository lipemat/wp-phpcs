<?php
/**
 * Lipe.JS.HTMLExecutingFunctions
 *
 * @package wp-phpcs\Lipe
 */

namespace Lipe\Sniffs\JS;

use Lipe\Traits\EscapeOutputFunctions;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Flags functions, which are executing HTML passed to it.
 */
class HTMLExecutingFunctionsSniff extends \WordPressVIPMinimum\Sniffs\JS\HTMLExecutingFunctionsSniff {
	use EscapeOutputFunctions;

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param integer $stackPtr The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process_token( $stackPtr ) : void {
		if ( ! isset( $this->HTMLExecutingFunctions[ $this->tokens[ $stackPtr ]['content'] ] ) ) {
			// Looking for specific functions only.
			return;
		}

		$functionToken = $this->phpcsFile->findNext( Tokens::$functionNameTokens, ( $stackPtr + 1 ) );
		if ( $this->isEscapeFunction( $functionToken ) ) {
			return;
		}

		parent::process_token( $stackPtr );
	}

}
