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
	 * @return array<int, (int|string)>
	 */
	public function register() {
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
	 * @return int|void Integer stack pointer to skip forward or void to continue
	 *                  normal file processing.
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		/**
		 * We only care about the first <?php tag in the file.
		 */
		if ( $phpcsFile->findPrevious( [ T_OPEN_TAG ], $stackPtr - 1, null, true ) !== false ) {
			return;
		}

		$declare = $phpcsFile->findNext( [ T_DECLARE ], $stackPtr + 1 );
		if ( false === $declare ) {
			return;
		}
		$type    = $phpcsFile->findNext( [ T_STRING ], $declare + 1 );
		$enabled = $phpcsFile->findNext( [ T_LNUMBER ], $declare + 1 );
		if ( \strtolower( $tokens[ $type ]['content'] ) === 'strict_types' && \strtolower( $tokens[ $enabled ]['content'] ) === '1'
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
			$max = $stackPtr + 10;
			for ( $i = $stackPtr; $i < $max; $i ++ ) {
				$phpcsFile->fixer->replaceToken( $i, '' );
			}
		}
	}

}
