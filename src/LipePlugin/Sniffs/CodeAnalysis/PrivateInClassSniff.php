<?php
/**
 * LipePlugin.TypeHints.PrivateInClass
 *
 * Used to verify a distributed plugin is not using `private` access modifiers.
 *
 * @package wp-phpcs\Lipe
 */

namespace LipePlugin\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;
use PHPCSUtils\Utils\Conditions;

/**
 * Forbid the `private` access modifier for any classes.
 */
class PrivateInClassSniff implements Sniff {

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var string[]
	 */
	public $supportedTokenizers = [ 'PHP' ];


	/**
	 * Look for <?php tags only.
	 * `declare` is always after the first one.
	 *
	 * @return array<int, (int|string)>
	 */
	public function register() {
		return [
			T_PRIVATE,
		];
	}


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile                        The file being scanned.
	 * @param int  $stackPtr                         The position of the current token
	 *                                               in the stack passed in $tokens.
	 *
	 * @return int|void Integer stack pointer to skip forward or void to continue
	 *                  normal file processing.
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		$next = $phpcsFile->findNext( Tokens::$emptyTokens, $stackPtr + 1, null, true );

		if ( T_FUNCTION === $tokens[ $next ]['code'] ) {
			$name = $tokens[ $phpcsFile->findNext( [ T_WHITESPACE ], $next + 1, null, true ) ]['content'];
		} else {
			$name = $tokens[ $phpcsFile->findNext( [ T_VARIABLE ], $next ) ]['content'];
		}
		$this->handleError( $phpcsFile, $stackPtr, 'Found', $name );
	}


	/**
	 * Throw and potentially fix the error.
	 *
	 * @since 1.0.0
	 *
	 * @param File   $phpcsFile The file being scanned.
	 * @param int    $stackPtr  The position of erroneous `T_SELF` token.
	 * @param string $errorCode The error code for the message.
	 * @param string $extraMsg  Addition to the error message.
	 *
	 * @return void
	 */
	private function handleError( $phpcsFile, $stackPtr, $errorCode, $extraMsg ) {
		$fix = $phpcsFile->addFixableWarning(
			'Use `private` access modifiers is not allowed in distributed plugins. Found: `%s`',
			$stackPtr,
			$errorCode,
			[ $extraMsg ]
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->replaceToken( $stackPtr, 'protected' );
		}
	}

}
