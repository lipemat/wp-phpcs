<?php

new WP_Query( [
	'orderby' => 'rand',
] );
new WP_Query( [
	'meta_value' => 'foo',
	'orderby'    => 'meta_value',
] );
new WP_Query( [
	'orderby' => 'meta_value_num',
] );

$args = new Args\WP_Query();
$args->orderby = 'meta_value';

$args = new Lipe\Lib\Query\Args();
$args->orderby = 'rand';

$what = new stdClass();
$what->orderby = 'meta_value_num';

$args = [
	'orderby' => 'rand',
];

$args2 = new Lipe\Lib\Query\Args();
$args2->orderby( 'meta_value' );
$args2->orderby( 'rand' );
$args2->orderby( 'meta_value_num' );

get_posts( [
	'orderby' => 'rand',
] );

$args = new Args\WP_Query();
$args->orderby = 'meta_value';

$args = new Args\WP_Query();
$args->orderby = 'rand';

$args = new Args\WP_Query();
$args->orderby = 'meta_value_num';
