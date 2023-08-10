<?php

new WP_Query( [
	'orderby' => 'modified',
] );
new WP_Query( [
	'orderby' => 'relevance',
] );
new WP_Query( [
	'orderby' => 'post__in',
] );

get_posts( [
	'orderby'          => 'modified',
	'suppress_filters' => false,
] );

$args2 = new Lipe\Lib\Query\Args();
$args2->orderby( 'title' );
$args2->orderby( 'parent' );

$args = new Args\WP_Query();
$args->orderby = 'title';

$args = new Args\WP_Query();
$args->orderby = 'post__in';

$args = new Args\WP_Query();
$args->orderby = 'modified';
