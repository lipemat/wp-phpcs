<?php
declare( strict_types=1 );

namespace Lipe\Tests\Performance;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * @author Mat Lipe
 * @since  August 2023
 *
 */
final class PostNotInUnitTest extends AbstractSniffUnitTest {
	/**
	 * Returns the lines where errors should occur.
	 *
	 * @return array <int line number> => <int number of errors>
	 */
	public function getErrorList(): array {
		$file = func_get_arg( 0 );
		switch ( $file ) {
			case 'PostNotInt.success.inc':
				return [];

			case 'PostNotInUnitTest.fail.inc':
				return [
				];
		}
		return [];
	}


	/**
	 * Returns the lines where warnings should occur.
	 *
	 * @return array <int line number> => <int number of warnings>
	 */
	public function getWarningList(): array {
		$file = func_get_arg( 0 );
		switch ( $file ) {
			case 'PostNotInUnitTest.success.inc':
				return [];

			case 'PostNotInUnitTest.fail.inc':
				return [
					6  => 1,
					9  => 1,
					13 => 1,
					17 => 1,
					20 => 1,
					23 => 1,
					24 => 1,
					27 => 1,
					32 => 1,
					37 => 1,
					41 => 1,
					45 => 1,
					46 => 1,
					50 => 1,
					51 => 1,
				];
		}
		return [];
	}

}
