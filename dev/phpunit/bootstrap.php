<?php
/**
 * Unit tests bootstrap file.
 *
 * @package Lipe
 */

// Setup some constants that PHPCS uses for testing.
define( 'PHP_CODESNIFFER_IN_TESTS', true );
define( 'PHP_CODESNIFFER_CBF', false );

// Check phpcs is installed.
$phpcs_dir = dirname( __DIR__, 2 ) . '/vendor/squizlabs/php_codesniffer';
if ( ! file_exists( $phpcs_dir ) ) {
	throw new Exception( 'Could not find PHP_CodeSniffer. Run `composer install --prefer-source`' );
}

// Require autoloader and bootstrap.
require __DIR__ . '/tests/SniffSuiteAbstract.php';
require dirname( __DIR__, 2 ) . '/vendor/autoload.php';
require $phpcs_dir . '/autoload.php';
require $phpcs_dir . '/tests/bootstrap.php';

// Pull in required abstract classes from wpcs.
// Note: these are in a necessary order for subclassing.
require dirname( __DIR__, 2 ) . '/vendor/wp-coding-standards/wpcs/WordPress/Sniff.php';
require dirname( __DIR__, 2 ) . '/vendor/wp-coding-standards/wpcs/WordPress/AbstractArrayAssignmentRestrictionsSniff.php';

$vip_dependencies = [
	'Sniffs/Sniff.php',
	'Sniffs/JS/StringConcatSniff.php',
	'Sniffs/JS/WindowSniff.php',
	'Sniffs\JS\StrippingTagsSniff.php',
	'Sniffs/JS/InnerHTMLSniff.php',
	'Sniffs/JS/DangerouslySetInnerHTMLSniff.php',
	'Sniffs/JS/HTMLExecutingFunctionsSniff.php',
];
foreach ( $vip_dependencies as $dependency ) {
	require dirname( __DIR__, 2 ) . '/vendor/automattic/vipwpcs/WordPressVIPMinimum/' . $dependency;
}
