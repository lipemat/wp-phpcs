<?php

use Lipe\Lib\Query\Get_Posts;

new WP_Query( [
	'post__not_in' => [ 1, 2, 3 ],
] );
new WP_Query( [
	'post__not_in' => [ 1, 2, 3 ],
	'orderby'      => 'meta_value',
] );
new WP_Query( [
	'post__not_in' => [ 1, 2, 3 ],
] );

$args = new Args\WP_Query();
$args->post__not_in = [ 1, 2, 3 ];

$args = new Lipe\Lib\Query\Args();
$args->post__not_in = [ 1, 2, 3 ];

$what = new stdClass();
$what->post__not_in = [ 1, 2, 3 ];
$what->exclude = [ 1, 2, 3 ];

$args = [
	'post__not_in'     => [ 1, 2, 3 ],
	'suppress_filters' => true,
];

$args2 = new Lipe\Lib\Query\Args();
$args2->post__not_in = [ 1, 2, 3 ];
$args->suppress_filters = true;

get_posts( [
	'suppress_filters' => true,
	'post__not_in'     => [ 1, 2, 3 ],
] );

get_posts( [
	'exclude' => [ 1, 2, 3 ],
] );

$args = new Get_Posts();
$args->exclude = [ 1, 2, 3 ];
$args->post__not_in = [ 1, 2, 3 ];
$args->suppress_filters = true;

$args = new \Args\get_posts();
$args->exclude = [ 1, 2, 3 ];
$args->post__not_in = [ 1, 2, 3 ];
$args->suppress_filters = true;
