<?php
declare( strict_types=1 );

namespace Lipe\Tests\DB;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the SlowOrderBy sniff.
 *
 * @since 3.1.0
 */
final class SlowOrderByUnitTest extends AbstractSniffUnitTest {
	protected function getErrorList() : array {
		$file = func_get_arg( 0 );
		switch ( $file ) {
			case 'SlowOrderByUnitTest.success.inc':
				return [];

			case 'SlowOrderByUnitTest.fail.inc':
				return [
					4  => 1,
					10 => 1,
					14 => 1,
				];
		}
		return [];
	}


	protected function getWarningList() : array {
		$file = func_get_arg( 0 );
		switch ( $file ) {
			case 'SlowOrderByUnitTest.success.inc':
				return [];
			case 'SlowOrderByUnitTest.fail.inc':
				return [];
		}
		return [];
	}

}
