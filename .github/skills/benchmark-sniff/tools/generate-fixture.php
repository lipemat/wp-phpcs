<?php
/**
 * Generate a realistic-size WordPress plugin file for benchmarking PHPCS sniffs.
 *
 * Produces ~2,000 LoC containing a mix of patterns each sniff cares about:
 * - get_posts / WP_Query / get_children calls with various argument shapes
 * - meta_query / orderby / post__not_in arrays
 * - fluent interfaces (johnbillion/args-style)
 * - filter_input + nonce-verifiable code paths
 * - `private` modifiers and `self::` usage
 * - declare(strict_types) candidates
 * - ternary / null coalesce in foreach / if
 */

declare( strict_types=1 );

function generate_benchmark_fixture( string $path ): void {
	$repeat = 80; // 2x the original to amplify signal above noise floor.
	$out = [];
	$out[] = "<?php\n";
	$out[] = "namespace Bench;\n";
	$out[] = "use Lipe\\Lib\\Query\\Args_Trait;\n\n";

	// Many small classes to exercise SelfInClassSniff + PrivateInClassSniff hot paths.
	for ( $c = 0; $c < 20; $c++ ) {
		$out[] = generate_helper_class( $c );
	}

	$out[] = "class Benchmark_Plugin {\n";
	$out[] = "    private \$cache = [];\n";
	$out[] = "    private static \$instance;\n\n";

	for ( $i = 0; $i < $repeat; $i++ ) {
		$out[] = generate_method( $i );
	}

	$out[] = "}\n\n";

	for ( $i = 0; $i < 20; $i++ ) {
		$out[] = generate_free_function( $i );
	}

	file_put_contents( $path, implode( '', $out ) );
}


function generate_helper_class( int $c ): string {
	return <<<PHP
class Helper_{$c} {
    private string \$name = 'helper_{$c}';
    private static int \$count = 0;

    public static function instance(): self {
        if ( null === self::\$count ) {
            self::\$count = 0;
        }
        return new self();
    }

    public function get( \$key ) {
        \$val = filter_input( INPUT_POST, \$key, FILTER_SANITIZE_STRING );
        if ( null === \$val ) {
            \$val = filter_input( INPUT_GET, \$key );
        }
        return self::class . ':' . ( \$val ?? '' );
    }

    private function _internal( \$x ) {
        return \$x instanceof self ? \$x : new self();
    }
}


PHP;
}

function generate_method( int $i ): string {
	$variants = [
		// Variant A: get_posts with array literal, suppress_filters missing
		<<<PHP
    public function method_get_posts_{$i}( int \$arg ) {
        \$args = [
            'post_type' => 'post',
            'posts_per_page' => 20,
            'meta_query' => [
                [ 'key' => 'featured', 'value' => '1', 'compare' => '=' ],
                [ 'key' => 'priority', 'value' => 5, 'compare' => '>=' ],
            ],
            'orderby' => 'meta_value_num',
            'post__not_in' => [ 1, 2, 3 ],
        ];
        \$posts = get_posts( \$args );
        if ( \$arg ?? null ) {
            return \$posts;
        }
        return [];
    }


PHP,
		// Variant B: WP_Query with meta_query nested
		<<<PHP
    public function method_wp_query_{$i}( \$context ) {
        \$query = new \\WP_Query( [
            'post_type' => 'page',
            'posts_per_page' => 50,
            'meta_query' => [
                'relation' => 'OR',
                [ 'key' => 'a', 'value' => 'x', 'compare' => 'LIKE' ],
                [ 'key' => 'b', 'value' => 'y' ],
            ],
            'orderby' => 'rand',
            'exclude' => [ 4, 5 ],
        ] );
        foreach ( \$query->posts ?? [] as \$post ) {
            echo esc_html( \$post->post_title );
        }
    }


PHP,
		// Variant C: filter_input + private + self
		<<<PHP
    private function method_filter_input_{$i}() {
        \$value = filter_input( INPUT_POST, 'field_{$i}', FILTER_SANITIZE_STRING );
        \$other = filter_input( INPUT_GET, 'q_{$i}' );
        if ( \$value ? true : false ) {
            return self::\$instance;
        }
        \$x = \$value ?? 'default';
        return \$x;
    }


PHP,
		// Variant D: fluent interface meta_query / orderby
		<<<PHP
    public function method_fluent_{$i}() {
        \$args = new \\Lipe\\Lib\\Query\\Get_Posts();
        \$args->post_type = 'post';
        \$args->posts_per_page = 10;
        \$args->meta_query = [
            [ 'key' => 'featured_{$i}', 'value' => '1', 'compare' => 'IN' ],
        ];
        \$args->orderby = 'meta_value';
        \$args->post__not_in = [ {$i} ];
        return get_posts( \$args->get_args() );
    }


PHP,
		// Variant E: SQL_CALC_FOUND_ROWS + many strings
		<<<PHP
    public function method_sql_{$i}( \$wpdb ) {
        \$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM {\$wpdb->posts} WHERE post_status = 'publish' LIMIT 10";
        \$results = \$wpdb->get_results( \$sql );
        \$benign = 'select * from foo where x = 1';
        return \$results;
    }


PHP,
	];

	return $variants[ $i % count( $variants ) ];
}

function generate_free_function( int $i ): string {
	return <<<PHP
function bench_free_{$i}( \$arr ) {
    foreach ( \$arr ?? [] as \$key => \$value ) {
        if ( \$value ?? false ) {
            continue;
        }
    }
    \$posts = get_posts( [ 'post_type' => 'thing_{$i}', 'suppress_filters' => false ] );
    return \$posts;
}


PHP;
}

if ( PHP_SAPI === 'cli' && realpath( $argv[0] ?? '' ) === __FILE__ ) {
	$target = __DIR__ . '/generated-fixture.php';
	generate_benchmark_fixture( $target );
	echo "Wrote {$target} (" . number_format( filesize( $target ) ) . " bytes)\n";
}
