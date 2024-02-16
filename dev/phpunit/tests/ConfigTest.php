<?php

namespace Lipe\Sniffs\Config;

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Ruleset;
use PHPUnit\Framework\TestCase;

/**
 * @author Mat Lipe
 * @since  February 2024
 *
 */
class ConfigTest extends TestCase {
	public function test_invalid(): void {
		$this->set_version( '4.5.3' );
		$this->expectExceptionMessage( 'The `minimum_wp_version` value in the phpcs.xml file must include a minor version only! Try 4.5 instead.' );
		$this->expectException( \LogicException::class );
		new Ruleset( $this->config );
	}


	public function test_non_number(): void {
		$this->set_version( '4.5.3-alpha' );
		$this->expectExceptionMessage( 'The `minimum_wp_version` value in the phpcs.xml file is not valid!' );
		$this->expectException( \LogicException::class );
		new Ruleset( $this->config );
	}


	public function test_valid(): void {
		$this->set_version( '5.0' );
		$ruleset = new Ruleset( $this->config );
		$this->assertInstanceOf( Ruleset::class, $ruleset );

		$this->set_version( '4.1' );
		$ruleset = new Ruleset( $this->config );

		$this->set_version( '4' );
		$ruleset = new Ruleset( $this->config );
		$this->assertInstanceOf( Ruleset::class, $ruleset );
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

		// We want to set up our tests to only load our standards in for testing.
		$this->config->sniffs = [
			'Lipe.Config.WpMinimumVersion',
		];
	}


	private function set_version( string $version ): void {
		$data = get_private_property( Config::class, 'configData' );
		$data['minimum_wp_version'] = $version;
		set_private_property( Config::class, 'configData', $data );
	}
}
