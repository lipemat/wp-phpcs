<?php

new WP_Query( [
	'orderby'        => 'rand',
	'posts_per_page' => 4,
] );

new WP_Query( [
	'post_type' => [ 'page', 'post' ],
	'orderby'   => 'meta_value',
] );

new WP_Query( [
	'orderby' => 'meta_value_num',
] );

$args = new Args\WP_Query();
$args->orderby = 'meta_value';
$args->post__in = [ 1, 2, 3, 4 ];
$args = new Args\WP_Query();
$args->orderby = 'rand';
$args = new Args\WP_Query();
$args->orderby = 'meta_value_num';

$args = new Lipe\Lib\Query\Args();
$args->orderby = 'rand';
$args->post__in = [ 1, 2, 3, 4 ];
$args = new Lipe\Lib\Query\Args();
$args->orderby = 'meta_value';
$args->paged = true;
$args = new Lipe\Lib\Query\Args();
$args->orderby = 'meta_value_num';

$what = new stdClass();
$what->orderby = 'meta_value_num';

$args = [
	'orderby' => 'rand',
];

$args2 = new Lipe\Lib\Query\Args();
$args2->orderby( 'meta_value' );
$args2->orderby( 'rand' );
$args2->orderby( 'meta_value_num' );
