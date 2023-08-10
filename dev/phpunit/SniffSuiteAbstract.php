<?php

use PHP_CodeSniffer\Autoload;
use PHP_CodeSniffer\Util\Standards;
use PHPUnit\Framework\TestSuite;
use PHPUnit\TextUI\TestRunner;

/**
 * Abstract class for sniff suites.
 * This class is used to run all unit tests for a specific standard.
 */
abstract class SniffSuiteAbstract {
	const TEST_SUFFIX = 'UnitTest.php';


	/**
	 * Prepare the test runner.
	 *
	 * @throws \ReflectionException - When would this throw?.
	 * @return void
	 */
	public static function main() {
		TestRunner::run( self::suite() );
	}


	/**
	 * Add all sniff unit tests into a test suite.
	 *
	 * @return TestSuite
	 */
	public static function suite() : TestSuite {
		$GLOBALS['PHP_CODESNIFFER_SNIFF_CODES'] = [];
		$GLOBALS['PHP_CODESNIFFER_FIXABLE_CODES'] = [];
		$GLOBALS['PHP_CODESNIFFER_SNIFF_CASE_FILES'] = [];

		$suite = new TestSuite( static::STANDARD . ' Standards' );

		$standards_dir = dirname( __DIR__, 2 ) . '/src';
		$all_details = Standards::getInstalledStandardDetails( false, $standards_dir );
		$details = $all_details[ static::STANDARD ];

		Autoload::addSearchPath( $details['path'], $details['namespace'] );
		Autoload::addSearchPath( $details['path'], $details['namespace'] );

		$test_dir = __DIR__ . '/tests/' . static::STANDARD . '/Tests/';
		if ( false === is_dir( $test_dir ) ) {
			// No tests for this standard.
			return $suite;
		}

		$di = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $test_dir, FilesystemIterator::SKIP_DOTS )
		);

		foreach ( $di as $file ) {
			$filename = $file->getFilename();

			// Skip hidden files.
			if ( 0 === strpos( $filename, '.' ) ) {
				continue;
			}

			// Tests must end with "Test.php".
			if ( substr( $filename, - 1 * strlen( static::TEST_SUFFIX ) ) !== static::TEST_SUFFIX ) {
				continue;
			}

			$className = Autoload::loadFile( $file->getPathname() );
			$GLOBALS['PHP_CODESNIFFER_STANDARD_DIRS'][ $className ] = $details['path'];
			$GLOBALS['PHP_CODESNIFFER_TEST_DIRS'][ $className ] = $test_dir;
			$suite->addTestSuite( $className );
		}

		return $suite;
	}
}
