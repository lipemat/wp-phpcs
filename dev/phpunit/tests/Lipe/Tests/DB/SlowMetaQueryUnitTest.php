<?php
/**
 * Lipe.DB.SlowMetaQuery
 *
 * @package Lipe
 */

declare( strict_types=1 );

namespace Lipe\Tests\DB;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Class SlowMetaQuerySniffTest
 */
class SlowMetaQueryUnitTest extends AbstractSniffUnitTest {
	/**
	 * Returns the lines where errors should occur.
	 *
	 * @return array <int line number> => <int number of errors>
	 */
	public function getErrorList() : array {
		$file = func_get_arg( 0 );
		switch ( $file ) {
			case 'SlowMetaQueryUnitTest.success.inc':
				return [];

			case 'SlowMetaQueryUnitTest.fail.inc':
				return [
					7   => 1,
					14  => 1,
					27  => 1,
					37  => 1,
					47  => 1,
					78  => 1,
					94  => 1,
					98  => 1,
					105 => 1,
				];
		}
		return [];
	}


	/**
	 * Returns the lines where warnings should occur.
	 *
	 * @return array <int line number> => <int number of warnings>
	 */
	public function getWarningList() : array {
		$file = func_get_arg( 0 );
		switch ( $file ) {
			case 'SlowMetaQueryUnitTest.success.inc':
				return [];

			case 'SlowMetaQueryUnitTest.fail.inc':
				return [
					59  => 1,
					69  => 1,
					84  => 1,
					90  => 1,
					110 => 1,
				];
		}
		return [];
	}

}
