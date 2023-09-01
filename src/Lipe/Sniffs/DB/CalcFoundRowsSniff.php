<?php
/**
 * Lipe.DB.CalcFoundRows
 *
 * MySQL has deprecated `SQL_CALC_FOUND_ROWS` in favor of running
 * a `COUNT` query instead.
 *
 * @link    https://dev.mysql.com/doc/refman/8.0/en/information-functions.html#function_found-rows
 * @link    https://core.trac.wordpress.org/ticket/47280
 *
 * @package wp-phpcs\Lipe
 */

namespace Lipe\Sniffs\DB;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Forbid `SQL_CALC_FOUND_ROWS` in all queries.
 */
class CalcFoundRowsSniff implements Sniff {
	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var string[]
	 */
	public $supportedTokenizers = [ 'PHP' ];


	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array<string|int>
	 */
	public function register(): array {
		return [
			\T_DOUBLE_QUOTED_STRING,
			\T_CONSTANT_ENCAPSED_STRING,
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

		if ( false === \stripos( $tokens[ $stackPtr ]['content'], 'SQL_CALC_FOUND_ROWS' ) ) {
			return;
		}

		$phpcsFile->addWarning(
			'Use `COUNT` instead of `SQL_CALC_FOUND_ROWS` in MySQL query.' . "\n" . '@see https://core.trac.wordpress.org/ticket/47280' . "\n",
			$stackPtr,
			'Found'
		);
	}
}
