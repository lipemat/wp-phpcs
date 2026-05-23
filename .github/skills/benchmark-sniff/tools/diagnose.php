<?php
declare( strict_types=1 );

define( 'PHP_CODESNIFFER_IN_TESTS', true );
define( 'PHP_CODESNIFFER_CBF', false );

$root = 'E:/SVN/wp-phpcs';
require $root . '/vendor/autoload.php';
require $root . '/vendor/squizlabs/php_codesniffer/autoload.php';
require $root . '/vendor/squizlabs/php_codesniffer/tests/bootstrap.php';
require $root . '/vendor/wp-coding-standards/wpcs/WordPress/Sniff.php';
require $root . '/vendor/wp-coding-standards/wpcs/WordPress/AbstractArrayAssignmentRestrictionsSniff.php';
require $root . '/vendor/phpcsstandards/phpcsextra/Universal/Helpers/DummyTokenizer.php';
PHP_CodeSniffer\Autoload::addSearchPath( $root . '/src/Lipe', 'Lipe' );
PHP_CodeSniffer\Autoload::addSearchPath( $root . '/src/LipePlugin', 'LipePlugin' );
PHP_CodeSniffer\Autoload::addSearchPath( $root . '/vendor/wp-coding-standards/wpcs/WordPress', 'WordPressCS\\WordPress' );
PHP_CodeSniffer\Autoload::addSearchPath( $root . '/vendor/automattic/vipwpcs/WordPressVIPMinimum', 'WordPressVIPMinimum' );
PHP_CodeSniffer\Autoload::addSearchPath( $root . '/vendor/phpcompatibility/php-compatibility/PHPCompatibility', 'PHPCompatibility' );
PHP_CodeSniffer\Autoload::addSearchPath( $root . '/vendor/phpcsstandards/phpcsextra/Universal', 'Universal' );

$fixture = __DIR__ . '/generated-fixture.php';

foreach ( [ 'Lipe.Config.WpMinimumVersion', 'LipePlugin.TypeHints.PreventStrictTypes', 'Lipe.DB.CalcFoundRows' ] as $code ) {
	$standard = explode( '.', $code )[0];
	$config = new PHP_CodeSniffer\Config( [ '--no-cache', '-q', '--standard=' . $standard, '--sniffs=' . $code, $fixture ] );
	$config->cache = false;
	$ruleset = new PHP_CodeSniffer\Ruleset( $config );
	echo "$code: " . count( $ruleset->sniffs ) . " sniffs, " . count( $ruleset->tokenListeners ) . " token listeners\n";

	$times = [];
	for ( $i = 0; $i < 3; $i++ ) {
		$start = hrtime( true );
		$f = new PHP_CodeSniffer\Files\LocalFile( $fixture, $ruleset, $config );
		$f->process();
		$times[] = ( hrtime( true ) - $start ) / 1e6;
	}
	echo '  Times: ' . implode( ', ', array_map( fn( $t ) => number_format( $t, 1 ), $times ) ) . " ms\n";
}
