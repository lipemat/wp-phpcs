<?php

use Lipe\Lib\Query\Get_Posts;

$args = new Get_Posts();
$args->suppress_filters = false;

get_posts( $args );

$args = new Lipe\Lib\Query\Get_Posts();
$args->suppress_filters = false;
get_posts( $args );

get_posts( [
	'post_type'        => 'page',
	'suppress_filters' => false,
] );

$array = [
	'post_type'        => 'page',
	'suppress_filters' => false,
];

get_posts( $array );

get_posts( [
	'post_type'        => 'page',
	'suppress_filters' => false,
] );

$args = new Get_Posts();
$args->suppress_filters = false;

get_posts( $args );

$args = new Get_Posts();
$args->suppress_filters = false;

get_posts( $args );

wp_get_recent_posts( [
	'post_type'        => 'page',
	'suppress_filters' => false,
] );

wp_get_recent_posts( [
	'post_type'        => 'page',
	'suppress_filters' => false,
] );

get_children( [
	'post_type'        => 'page',
	'suppress_filters' => false,
] );
