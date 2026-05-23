<?php
/**
 * Per-sniff micro-benchmark.
 *
 * Measures the wall-clock cost of running each Lipe / LipePlugin sniff against
 * a synthetic, realistic-size WordPress plugin file. Each sniff runs in
 * isolation (a Ruleset configured with just that one sniff) so the numbers
 * reflect that sniff's own cost, not interactions.
 *
 * Usage (from project root):
 *   D:\xampp\php-8.4\php.exe .github\skills\benchmark-sniff\tools\benchmark.php [iterations] [warmup]
 *
 * Defaults: 5 iterations, 1 warmup run.
 */

declare( strict_types=1 );

use PHP_CodeSniffer\Autoload;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\LocalFile;
use PHP_CodeSniffer\Ruleset;

define( 'PHP_CODESNIFFER_IN_TESTS', true );
define( 'PHP_CODESNIFFER_CBF', false );

$root = dirname( __DIR__, 4 );
require $root . '/vendor/autoload.php';
require $root . '/vendor/squizlabs/php_codesniffer/autoload.php';
require $root . '/vendor/squizlabs/php_codesniffer/tests/bootstrap.php';
require $root . '/vendor/wp-coding-standards/wpcs/WordPress/Sniff.php';
require $root . '/vendor/wp-coding-standards/wpcs/WordPress/AbstractArrayAssignmentRestrictionsSniff.php';
require $root . '/vendor/phpcsstandards/phpcsextra/Universal/Helpers/DummyTokenizer.php';

Autoload::addSearchPath( $root . '/src/Lipe', 'Lipe' );
Autoload::addSearchPath( $root . '/src/LipePlugin', 'LipePlugin' );
Autoload::addSearchPath( $root . '/vendor/wp-coding-standards/wpcs/WordPress', 'WordPressCS\\WordPress' );
Autoload::addSearchPath( $root . '/vendor/automattic/vipwpcs/WordPressVIPMinimum', 'WordPressVIPMinimum' );
Autoload::addSearchPath( $root . '/vendor/phpcompatibility/php-compatibility/PHPCompatibility', 'PHPCompatibility' );
Autoload::addSearchPath( $root . '/vendor/phpcsstandards/phpcsextra/Universal', 'Universal' );

$iterations = (int) ( $argv[1] ?? 5 );
$warmup     = (int) ( $argv[2] ?? 1 );

$fixture = __DIR__ . '/generated-fixture.php';
if ( ! file_exists( $fixture ) || filemtime( __DIR__ . '/generate-fixture.php' ) > filemtime( $fixture ) ) {
	require __DIR__ . '/generate-fixture.php';
	generate_benchmark_fixture( $fixture );
}
$file_size = filesize( $fixture );
$line_count = substr_count( file_get_contents( $fixture ), "\n" );

$sniffs = [
	'Lipe.Config.WpMinimumVersion',
	'Lipe.DB.CalcFoundRows',
	'Lipe.Performance.PostNotIn',
	'Lipe.Performance.SlowMetaQuery',
	'Lipe.Performance.SlowOrderBy',
	'Lipe.Performance.SuppressFilters',
	'Lipe.PHP.DisallowNullCoalesceInCondition',
	'Lipe.PHP.DisallowNullCoalesceInForLoops',
	'Lipe.Security.NonceVerification',
	'Lipe.WhiteSpace.OperatorSpacing',
	'LipePlugin.CodeAnalysis.PrivateInClass',
	'LipePlugin.CodeAnalysis.SelfInClass',
	'LipePlugin.TypeHints.PreventStrictTypes',
];

echo "Benchmark fixture: {$fixture}\n";
echo 'Size: ' . number_format( $file_size ) . " bytes, ~{$line_count} lines\n";
echo "Iterations: {$iterations} (after {$warmup} warmup)\n\n";
echo str_pad( 'Sniff', 48 ) . str_pad( 'min (ms)', 12 ) . str_pad( 'median (ms)', 14 ) . "max (ms)\n";
echo str_repeat( '-', 86 ) . "\n";

$results = [];
foreach ( $sniffs as $sniff_code ) {
	$standard = explode( '.', $sniff_code )[0];

	// Build ruleset ONCE per sniff (outside timing loop). The Ruleset constructor
	// loads WPCS + every dependency, which dwarfs the per-file scan cost.
	// IMPORTANT: --sniffs= does NOT restrict execution to just the listed sniffs in
	// programmatic Ruleset construction; it only affects CLI display. We manually
	// strip $sniffs and $tokenListeners to the target class so the timed run truly
	// measures just one sniff.
	set_private_static( Config::class, 'configData', null );
	$config = new Config( [ '--no-cache', '-q', '--standard=' . $standard, $fixture ] );
	$config->cache = false;
	$ruleset = new Ruleset( $config );

	// Find the target sniff class & restrict the ruleset to it.
	$target_class = null;
	foreach ( $ruleset->sniffs as $class => $instance ) {
		if ( false !== strpos( $class, sniff_class_suffix( $sniff_code ) ) ) {
			$target_class = $class;
			break;
		}
	}
	if ( null === $target_class ) {
		fwrite( STDERR, "Could not locate sniff class for {$sniff_code}\n" );
		continue;
	}
	$ruleset->sniffs = [ $target_class => $ruleset->sniffs[ $target_class ] ];
	$instance = $ruleset->sniffs[ $target_class ];
	$tokens = $instance->register();
	$listeners = [];
	foreach ( $tokens as $token ) {
		$listeners[ $token ] = [
			$target_class => [
				'class'      => $target_class,
				'source'     => $sniff_code,
				'tokenizers' => [ 'PHP' => 'PHP' ],
				'ignore'     => [],
				'include'    => [],
			],
		];
	}
	$ruleset->tokenListeners = $listeners;

	$times = [];
	for ( $i = 0; $i < $iterations + $warmup; $i++ ) {
		$start = hrtime( true );
		$phpcsFile = new LocalFile( $fixture, $ruleset, $config );
		$phpcsFile->process();
		$elapsed_ms = ( hrtime( true ) - $start ) / 1_000_000;
		if ( $i >= $warmup ) {
			$times[] = $elapsed_ms;
		}
	}

	sort( $times );
	$min = $times[0];
	$max = end( $times );
	$median = $times[ (int) floor( count( $times ) / 2 ) ];
	$results[ $sniff_code ] = [
		'min'    => $min,
		'median' => $median,
		'max'    => $max,
	];

	printf( "%-48s%-12s%-14s%s\n", $sniff_code, format_ms( $min ), format_ms( $median ), format_ms( $max ) );
}

echo "\nTotal median (per-file scan only, ruleset construction excluded): " . format_ms( array_sum( array_column( $results, 'median' ) ) ) . " ms\n";

function sniff_class_suffix( string $code ): string {
	// "Lipe.Performance.SuppressFilters" -> "Sniffs\\Performance\\SuppressFiltersSniff"
	$parts = explode( '.', $code );
	array_shift( $parts );
	$last = array_pop( $parts );
	return 'Sniffs\\' . implode( '\\', $parts ) . '\\' . $last . 'Sniff';
}

function format_ms( float $ms ): string {
	return number_format( $ms, 2 );
}

function set_private_static( string $class, string $property, $value ): void {
	$ref = new ReflectionClass( $class );
	$prop = $ref->getProperty( $property );
	$prop->setAccessible( true );
	$prop->setValue( null, $value );
}
