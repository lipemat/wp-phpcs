<?php

// Basic meta query.
new WP_Query(
	[
		'meta_key'   => 'foo',
		'meta_value' => 'bar',
	]
);

// Advanced meta query.
new WP_Query(
	[
		'meta_query' => [
			'key'   => 'foo',
			'value' => 'bar',
		],
	]
);

// Custom compares.
new WP_Query(
	[
		'meta_query' => [
			'key'     => 'foo',
			'value'   => 'bar',
			'compare' => '!=',
		],
	]
);

new WP_Query(
	[
		'meta_query' => [
			'key'     => 'foo',
			'value'   => 'bar',
			'compare' => '>',
		],
	]
);

new WP_Query(
	[
		'meta_query' => [
			'key'     => 'foo',
			'value'   => 'bar',
			'compare' => 'LIKE',
		],
	]
);

// Variables.
$compare = 'LIKE';
new WP_Query(
	[
		'meta_query' => [
			'key'     => 'foo',
			'value'   => 'bar',
			'compare' => $compare,
		],
	]
);
$meta_query = [
	'key'   => 'foo',
	'value' => 'bar',
];
new WP_Query(
	[
		'meta_query' => $meta_query,
	]
);

new WP_Query( [
	'meta_key'     => 'foo',
	'orderby'      => 'meta_value',
	'meta_value'   => 'bar',
	'meta_compare' => 'EXISTS',
] );

$args = new Args\WP_Query();
$args->meta_value = 'bar';

// Only partially supported.
$clause = new Args\MetaQuery\Clause();
$clause->key = 'foo';
$clause->value = 'bar';
$args->meta_query = $clause;

$args = new Lipe\Lib\Query\Args();
$args->meta_query()
     ->equals( 'foo', 'bar' );

$args = new Lipe\Lib\Query\Args();
$args->meta_query()
     ->between( 'foo', [ 'bar', 'rock' ] );

$args = new Lipe\Lib\Query\Args();
$args->meta_query()
     ->not_exists( 'foo' );

$args = new Lipe\Lib\Query\Args();
$args->meta_query()
     ->exists( 'foo' );

$what = new stdClass();
$what->meta_value = 'bar';

$args = [
	'meta_value' => 'bar',
];

$what->meta_query = [
	[
		'key'     => 'foo',
		'value'   => 'bar',
		'compare' => 'EXISTS',
	],
];

$what = new stdClass();
$what->meta_query = [
	[
		'key'     => 'foo',
		'value'   => 'bar',
		'compare' => 'LIKE',
	],
];

use Args\get_posts;

$dynamic = new get_posts();
$array_clause = [
	'key'     => 'foo',
	'compare' => '!=',
];

$dynamic->meta_query = $array_clause;

$compare = 'EXISTS';
$first_level = $compare;
$deep_dynamic = new get_posts();
$array_clause = [
	'key'     => 'foo',
	'compare' => '=',
];

$deep_dynamic->meta_query = $array_clause;

$outside = new stdClass();
$outside->value = 'NOT EXISTS';

$out_dynamic = new get_posts();
$out_clause = [
	'key'     => 'foo',
	'compare' => $outside->value, // Can't determine this.
];

$out_dynamic->meta_query = $out_clause;
