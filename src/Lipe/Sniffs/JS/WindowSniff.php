<?php
/**
 * Lipe.JS.WindowSniff
 *
 * @package wp-phpcs\Lipe
 */

namespace Lipe\Sniffs\JS;

use Lipe\Abstracts\AbstractEscapeOutputFunctions;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Looks for instances of window properties that should be flagged.
 */
class WindowSniff extends AbstractEscapeOutputFunctions {

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
	public function process_token( $stackPtr ): void {
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

		$property = \str_replace( [ '"', "'" ], '', $this->tokens[ $nextNextTokenPtr ]['content'] );
		if ( ! is_string( $property ) || ! isset( $this->windowProperties[ $property ] ) ) {
			// Not in $windowProperties, bail.
			return;
		}

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

		$third_tokenPtr = $this->phpcsFile->findNext( array_merge( [ T_CLOSE_SQUARE_BRACKET ], Tokens::$emptyTokens ), $nextNextTokenPtr + 1, null, true, null, true );
		if ( false === $third_tokenPtr ) {
			// Something went wrong, bail.
			return;
		}
		$third_tokenCode = $this->tokens[ $third_tokenPtr ]['code'];

		$fourth_token = false;
		if ( T_OBJECT_OPERATOR === $third_tokenCode || T_OPEN_SQUARE_BRACKET === $third_tokenCode ) {
			$fourth_tokenPtr = $this->phpcsFile->findNext( Tokens::$emptyTokens, $third_tokenPtr + 1, null, true, null, true );
			if ( false === $fourth_tokenPtr ) {
				// Something went wrong, bail.
				return;
			}

			$fourth_token = str_replace( [ '"', "'" ], '', $this->tokens[ $fourth_tokenPtr ]['content'] );
			if ( ! is_string( $fourth_token ) || ! isset( $this->windowProperties[ $property ][ $fourth_token ] ) ) {
				// Not in $windowProperties, bail.
				return;
			}
		}

		$windowProperty = 'window.';
		$windowProperty .= is_string( $fourth_token ) ? $property . '.' . $fourth_token : $property;
		$data = [ $windowProperty ];

		$prevTokenPtr = $this->phpcsFile->findPrevious( Tokens::$emptyTokens, $stackPtr - 1, null, true, null, true );

		if ( T_EQUAL === $this->tokens[ $prevTokenPtr ]['code'] ) {
			// Variable assignment.
			$message = 'Data from JS global "%s" may contain user-supplied values and should be checked.';
			$this->phpcsFile->addWarning( $message, $stackPtr, 'VarAssignment', $data );

			return;
		}

		$message = 'Data from JS global "%s" may contain user-supplied values and should be sanitized before output to prevent XSS.';
		$this->phpcsFile->addError( $message, $stackPtr, $property, $data );
	}

}
