<?php

/**
 * Run tests on fixture files against the full lipe ruleset.xml
 *
 * For testing the results when the full `Lipe` rule is applied including
 * the 3rd party sniffs and exclusions therein.
 *
 * - Testing if a 3rd party rule is active.
 * - Testing if a 3rd party rule is excluded.
 * - Testing if errors are being reported from rules not under our control.
 *
 * @usage Add fixture files under `tests/fixtures/ruleset/Lipe`.
 */

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Ruleset;
use PHP_CodeSniffer\Tests\ConfigDouble;

require_once __DIR__ . '/Fixtures.php';

/**
 * Class tests\RulesetTests
 *
 * @group  ruleset
 */
class RulesetFixtureLipe extends Fixtures {
	/**
	 * Get files from the pass fixtures directory.
	 *
	 * @return array List of parameters to provide.
	 */
	public static function failing_files(): array {
		$directory = dirname( __DIR__ ) . '/fixtures/ruleset/Lipe/fail';

		return static::get_files_from_dir( $directory );
	}


	/**
	 * Get files from the pass fixtures directory.
	 *
	 * @return array List of parameters to provide.
	 */
	public static function passing_files(): array {
		$directory = dirname( __DIR__ ) . '/fixtures/ruleset/Lipe/pass';

		return static::get_files_from_dir( $directory );
	}


	/**
	 * Setup our ruleset.
	 */
	public function setUp(): void {
		set_private_property( Config::class, 'configData', null );

		$this->config = new Config();
		$this->config->cache = false;
		$this->config->standards = [ 'Lipe' ];

		$this->config->tabWidth = 4;

		$this->ruleset = new Ruleset( $this->config );
	}
}
