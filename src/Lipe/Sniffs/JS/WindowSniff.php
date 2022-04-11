<?php
/**
 * Lipe.JS.WindowSniff
 *
 * @package wp-phpcs\Lipe
 */

namespace Lipe\Sniffs\JS;

use Lipe\Traits\EscapeOutputFunctions;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Looks for instances of window properties that should be flagged.
 */
class WindowSniff extends \WordPressVIPMinimum\Sniffs\JS\WindowSniff {

	use EscapeOutputFunctions;

	/**
	 * List of window properties that need to be flagged.
	 *
	 * @var array
	 */
	private $windowProperties = [
		'location' => [
			'href'     => true,
			'protocol' => true,
			'host'     => true,
			'hostname' => true,
			'pathname' => true,
			'search'   => true,
			'hash'     => true,
			'username' => true,
			'port'     => true,
			'password' => true,
		],
		'name'     => true,
		'status'   => true,
	];


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param integer $stackPtr The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process_token( $stackPtr ) {
		if ( $this->tokens[ $stackPtr ]['content'] !== 'window' ) {
			// Doesn't begin with 'window', bail.
			return;
		}

		$nextTokenPtr = $this->phpcsFile->findNext( Tokens::$emptyTokens, ( $stackPtr + 1 ), null, true, null, true );
		$nextToken    = $this->tokens[ $nextTokenPtr ]['code'];
		if ( $nextToken !== T_OBJECT_OPERATOR && $nextToken !== T_OPEN_SQUARE_BRACKET ) {
			// No . or [' next, bail.
			return;
		}

		$nextNextTokenPtr = $this->phpcsFile->findNext( Tokens::$emptyTokens, ( $nextTokenPtr + 1 ), null, true, null, true );
		if ( $nextNextTokenPtr === false ) {
			// Something went wrong, bail.
			return;
		}

		$nextNextToken = str_replace( [ '"', "'" ], '', $this->tokens[ $nextNextTokenPtr ]['content'] );
		if ( isset( $this->windowProperties[ $nextNextToken ] ) ) {
			$functionToken = $this->phpcsFile->findNext( Tokens::$functionNameTokens, ( $nextNextTokenPtr + 3 ) );

			if ( $this->isEscapeFunction( $functionToken ) ) {
				return;
			}
		}

		parent::process_token( $stackPtr );
	}

}
