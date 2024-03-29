<?php
/**
 * Unit tests bootstrap file.
 *
 * @package Lipe
 */

// Setup some constants that PHPCS uses for testing.
use PHP_CodeSniffer\Autoload;

define( 'PHP_CODESNIFFER_IN_TESTS', true );
define( 'PHP_CODESNIFFER_CBF', false );

// Check phpcs is installed.
$phpcs_dir = dirname( __DIR__, 2 ) . '/vendor/squizlabs/php_codesniffer';
if ( ! file_exists( $phpcs_dir ) ) {
	throw new \RuntimeException( 'Could not find PHP_CodeSniffer. Run `composer install --prefer-source`' );
}

// Require the autoloader and bootstrap.
require __DIR__ . '/helpers.php';
require dirname( __DIR__, 2 ) . '/vendor/autoload.php';
require $phpcs_dir . '/autoload.php';
require $phpcs_dir . '/tests/bootstrap.php';

// Pull in required abstract classes from wpcs.
// Note: these are in a necessary order for subclassing.
require dirname( __DIR__, 2 ) . '/vendor/wp-coding-standards/wpcs/WordPress/Sniff.php';
require dirname( __DIR__, 2 ) . '/vendor/wp-coding-standards/wpcs/WordPress/AbstractArrayAssignmentRestrictionsSniff.php';
require dirname( __DIR__, 2 ) . '/vendor/phpcsstandards/phpcsextra/Universal/Helpers/DummyTokenizer.php';

// Add paths of vendor sniffs to the autoloader.
Autoload::addSearchPath( dirname( __DIR__, 2 ) . '/src/Lipe', 'Lipe' );
Autoload::addSearchPath( dirname( __DIR__, 2 ) . '/src/LipePlugin', 'LipePlugin' );
Autoload::addSearchPath( dirname( __DIR__, 2 ) . '/vendor/wp-coding-standards/wpcs/WordPress', 'WordPressCS\WordPress' );
Autoload::addSearchPath( dirname( __DIR__, 2 ) . '/vendor/automattic/vipwpcs/WordPressVIPMinimum', 'WordPressVIPMinimum' );
Autoload::addSearchPath( dirname( __DIR__, 2 ) . '/vendor/phpcompatibility/php-compatibility/PHPCompatibility', 'PHPCompatibility' );
Autoload::addSearchPath( dirname( __DIR__, 2 ) . '/vendor/phpcsstandards/phpcsextra/Universal', 'Universal' );

require __DIR__ . '/HelpersAbstract.php';
require __DIR__ . '/SniffSuiteAbstract.php';
