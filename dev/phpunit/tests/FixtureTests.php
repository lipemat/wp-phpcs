<?php
/**
 * Run tests on fixture files against our custom standards.
 *
 * This test suite runs our standards against files which have
 * known errors or known passing conditions. We run these tests
 * against said fixture files as it's closer to real-world conditions
 * than isolated unit tests and provides another layer of security.
 */

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Ruleset;
use PHP_CodeSniffer\Files\LocalFile;
use PHPUnit\Framework\TestCase;

/**
 * Class FixtureTests
 *
 * @group fixtures
 */
class FixtureTests extends TestCase {
	/**
	 * Config instance.
	 *
	 * @var Config
	 */
	protected $config;

	/**
	 * Ruleset instance.
	 *
	 * @var Ruleset
	 */
	protected $ruleset;


	/**
	 * Get a lit of files from a directory path.
	 *
	 * @param string $directory Directory to recursively look through.
	 *
	 * @return array List of files to run.
	 */
	public static function get_files_from_dir( string $directory ) : array {
		$files = [];
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $directory )
		);

		foreach ( $iterator as $path => $file ) {
			if ( ! $file->isFile() || 'json' === $file->getExtension() || '.gitkeep' === $file->getFilename() ) {
				continue;
			}

			$files[] = [ $path ];
		}

		return $files;
	}


	/**
	 * Get files from the pass fixtures directory.
	 *
	 * @return array List of parameters to provide.
	 */
	public static function failing_files() : array {
		$directory = __DIR__ . '/fixtures/fail';

		return static::get_files_from_dir( $directory );
	}


	/**
	 * Get files from the pass fixtures directory.
	 *
	 * @return array List of parameters to provide.
	 */
	public static function passing_files() : array {
		$directory = __DIR__ . '/fixtures/pass';

		return static::get_files_from_dir( $directory );
	}


	/**
	 * Setup our ruleset.
	 */
	public function setUp() {
		$this->config = new Config();
		$this->config->cache = false;
		$this->config->standards = [ 'Lipe' ];
		$this->config->tabWidth = 4;

		// We want to set up our tests to only load our standards in for testing.
		$this->config->sniffs = [
			'Lipe.DB.CalcFoundRows',
			'Lipe.Performance.PostNotIn',
			'Lipe.Performance.SlowMetaQuery',
			'Lipe.Performance.SlowOrderBy',
			'Lipe.JS.DangerouslySetInnerHTML',
			'Lipe.JS.HTMLExecutingFunctions',
			'Lipe.JS.InnerHTML',
			'Lipe.JS.StringConcat',
			'Lipe.JS.StrippingTags',
			'Lipe.JS.Window',
			'Lipe.PHP.DisallowNullCoalesceInCondition',
		];

		$this->ruleset = new Ruleset( $this->config );
	}


	/**
	 * @dataProvider passing_files
	 */
	public function test_passing_files( $file ) {
		$phpcsFile = new LocalFile( $file, $this->ruleset, $this->config );
		$phpcsFile->process();

		$rel_file = substr( $file, strlen( __DIR__ ) );
		$foundErrors = $phpcsFile->getErrors();
		$this->assertEquals( [], $foundErrors, sprintf( 'File %s should not contain any errors', $rel_file ) );
		$foundWarnings = $phpcsFile->getWarnings();
		$this->assertEquals( [], $foundWarnings, sprintf( 'File %s should not contain any warnings', $rel_file ) );
	}


	/**
	 * @dataProvider failing_files
	 */
	public function test_failing_files( $file ) {
		$phpcsFile = new LocalFile( $file, $this->ruleset, $this->config );
		$phpcsFile->process();

		$rel_file = substr( $file, strlen( __DIR__ ) );
		$foundErrors = $phpcsFile->getErrors();
		$foundWarnings = $phpcsFile->getWarnings();

		$expected_file = $file . '.json';
		$expected = json_decode( file_get_contents( $expected_file ), true );

		$this->assertEquals(
			JSON_ERROR_NONE,
			json_last_error(),
			sprintf(
				'Expected JSON should be correctly parsed: %s',
				json_last_error_msg()
			)
		);

		$found = [];
		foreach ( $foundErrors as $line => $columns ) {
			foreach ( $columns as $column => $errors ) {
				foreach ( $errors as $error ) {
					$found[ $line ][] = [
						'source' => $error['source'],
						'type'   => 'error',
					];
				}
			}
		}
		foreach ( $foundWarnings as $line => $columns ) {
			foreach ( $columns as $column => $errors ) {
				foreach ( $errors as $error ) {
					$found[ $line ][] = [
						'source' => $error['source'],
						'type'   => 'warning',
					];
				}
			}
		}

		$this->assertEquals( $expected, $found, sprintf( 'File %s should only contain specified errors', $rel_file ) );
	}
}
