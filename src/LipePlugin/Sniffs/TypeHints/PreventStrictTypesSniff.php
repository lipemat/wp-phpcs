<?php
/**
 * LipePlugin.TypeHints.PreventStrictTypes
 *
 * Used to verify a distributed plugin is not using `declare(strict_types = 1)` which
 * cannot be relied on with 3rd party plugins in the stack.
 *
 * @package wp-phpcs\Lipe
 */

namespace LipePlugin\Sniffs\TypeHints;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Forbid the `declare(strict_types = 1)` statement.
 */
class PreventStrictTypesSniff implements Sniff {

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
	 * @return array<int|string>
	 */
	public function register(): array {
		return [
			T_OPEN_TAG,
		];
	}


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile                        The file being scanned.
	 * @param int  $stackPtr                         The position of the current token
	 *                                               in the stack passed in $tokens.
	 *
	 * @return void Integer stack pointer to skip forward or void to continue
	 *                  normal file processing.
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		/**
		 * We only care about the first <?php tag in the file.
		 */
		if ( false !== $phpcsFile->findPrevious( [ T_OPEN_TAG ], $stackPtr - 1, null, true ) ) {
			return;
		}

		$declare = $phpcsFile->findNext( [ T_DECLARE ], $stackPtr + 1 );
		if ( false === $declare ) {
			return;
		}
		$type    = $phpcsFile->findNext( [ T_STRING ], $declare + 1 );
		$enabled = $phpcsFile->findNext( [ T_LNUMBER ], $declare + 1 );
		if ( 'strict_types' === \strtolower( $tokens[ $type ]['content'] ) && '1' === \strtolower( $tokens[ $enabled ]['content'] )
		) {
			$this->handleError( $phpcsFile, $declare, 'Found', 'declare( strict_types=1 );' );
		}
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
		$fix = $phpcsFile->addFixableError(
			'Declaring `strict_types` is not allowed in distributed plugins. Found: %s',
			$stackPtr,
			$errorCode,
			[ $extraMsg ]
		);

		if ( true === $fix ) {
			$semicolon = $phpcsFile->findNext( [ T_SEMICOLON ], $stackPtr );
			if ( false !== $semicolon ) {
				$end = $semicolon + 1;
				for ( $i = $stackPtr; $i <= $end; $i ++ ) {
					$phpcsFile->fixer->replaceToken( $i, '' );
				}
			}
		}
	}
}
