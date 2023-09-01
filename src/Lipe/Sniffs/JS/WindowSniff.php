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
	 * @var array<string, bool|array<string,bool>>
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
	public function process_token( $stackPtr ) : void {
		if ( 'window' !== $this->tokens[ $stackPtr ]['content'] ) {
			// Doesn't begin with 'window', bail.
			return;
		}

		$nextTokenPtr = $this->phpcsFile->findNext( Tokens::$emptyTokens, ( $stackPtr + 1 ), null, true, null, true );
		if ( false === $nextTokenPtr ) {
			return;
		}
		$nextToken = $this->tokens[ $nextTokenPtr ]['code'];
		if ( T_OBJECT_OPERATOR !== $nextToken && T_OPEN_SQUARE_BRACKET !== $nextToken ) {
			// No . or [' next, bail.
			return;
		}

		$nextNextTokenPtr = $this->phpcsFile->findNext( Tokens::$emptyTokens, ( $nextTokenPtr + 1 ), null, true, null, true );
		if ( false === $nextNextTokenPtr ) {
			// Something went wrong, bail.
			return;
		}

		$nextNextToken = \str_replace( [ '"', "'" ], '', $this->tokens[ $nextNextTokenPtr ]['content'] );

		if ( is_string( $nextNextToken ) && isset( $this->windowProperties[ $nextNextToken ] ) ) {
			$functionToken = $this->phpcsFile->findNext( Tokens::$functionNameTokens, ( $nextNextTokenPtr + 3 ) );

			// Wrapped in escape function.
			if ( isset( $this->tokens[ $nextTokenPtr ]['nested_parenthesis'] ) ) {
				$functionBefore = $this->phpcsFile->findNext( Tokens::$functionNameTokens, $stackPtr - 3 );
				if ( $this->isEscapeFunction( $functionBefore ) ) {
					return;
				}
			}

			// Followed by escape function.
			if ( $this->isEscapeFunction( $functionToken ) ) {
				return;
			}
		}

		parent::process_token( $stackPtr );
	}

}
