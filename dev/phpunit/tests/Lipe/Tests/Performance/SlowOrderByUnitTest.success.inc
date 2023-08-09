<?php

new WP_Query( [
	'orderby'        => 'ID',
	'posts_per_page' => 4,
] );

new WP_Query( [
	'post_type' => [ 'page', 'post' ],
	'orderby'   => 'title',
] );

new WP_Query( [
	'orderby' => 'date',
] );

get_posts( [
	'orderby' => 'modified',
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
