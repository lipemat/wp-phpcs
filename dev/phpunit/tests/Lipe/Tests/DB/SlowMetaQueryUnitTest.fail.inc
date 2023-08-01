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

get_posts(
	[
		'meta_query' => [
			'key'     => 'foo',
			'value'   => 'bar',
			'compare' => '!=',
		],
	]
);

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

$what = new stdClass();
$what->meta_query = [
	[
		'key'     => 'foo',
		'value'   => 'bar',
		'compare' => 'LIKE',
	],
];

$what = new stdClass();
$what->meta_value = 'bar';
