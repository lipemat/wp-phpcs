<?php

// Key without value is OK (albeit useless).
new WP_Query( [
	'meta_key' => 'foo',
] );

// EXISTS/NOT EXISTS are performant.
new WP_Query( [
	'meta_query' => [
		'key'     => 'foo',
		'compare' => 'EXISTS',
	],
] );
new WP_Query( [
	'meta_query' => [
		'key'     => 'foo',
		'compare' => 'NOT EXISTS',
	],
] );

// Specifying relation is OK.
new WP_Query( [
	'meta_query' => [
		'relation' => 'AND',
		[
			'key'     => 'foo',
			'compare' => 'EXISTS',
		],
		[
			'key'     => 'bar',
			'compare' => 'NOT EXISTS',
		],
	],
] );
new WP_Query( [
	'meta_query' => [
		'relation' => 'OR',
		[
			'key'     => 'foo',
			'compare' => 'NOT EXISTS',
		],
		[
			'key'     => 'bar',
			'compare' => 'EXISTS',
		],
	],
] );
$relation = 'OR';
new WP_Query( [
	'meta_query' => [
		'relation' => $relation,
		[
			'key'     => 'foo',
			'compare' => 'EXISTS',
		],
	],
] );

// Ignores should work.
new WP_Query( [
	'meta_query' => [
		'key'     => 'foo',
		'value'   => 'bar',
		// phpcs:ignore Lipe.Performance.SlowMetaQuery.NonPerformant -- Only a few records, so performant.
		'compare' => '!=',
	],
] );

$meta_query = [
	'key'     => 'foo',
	'compare' => 'EXISTS',
];
new WP_Query( [
	// phpcs:ignore Lipe.Performance.SlowMetaQuery.Dynamic -- See above, performant.
	'meta_query' => $query,
] );