<?php

use Lipe\Lib\Query\Get_Posts;

$args = new Get_Posts();
$args->suppress_filters = true;

get_posts( $args );

$args = new Lipe\Lib\Query\Get_Posts();
$args->suppress_filters = false;
get_posts( $args );

get_posts();

get_posts( [
	'post_type' => 'page',
	'unknown'   => 'not-used',
] );

get_posts( [
	'post_type'        => 'page',
	'suppress_filters' => true,
] );

$array = [
	'post_type'        => 'page',
	'suppress_filters' => true,
];

get_posts( $array );

get_posts( [
	'post_type'        => 'page',
	'suppress_filters' => false,
] );

$args = new Get_Posts();
$args->exclude = [ 1, 2, 3 ];

get_posts( $args );

$args = new Get_Posts();
$args->suppress_filters = true;

get_posts( $args );

$args = new Get_Posts();
$args->suppress_filters = false;

get_posts( $args );

wp_get_recent_posts( array(
	'post_type'        => 'page',
	'suppress_filters' => true,
) );

wp_get_recent_posts( [
	'post_type' => 'page',
] );

get_children( [
	'post_type'        => 'page',
	'suppress_filters' => true,
] );

get_children( [
	'post_type' => 'page',
] );

$array = array(
	'post_type'        => 'page',
	'suppress_filters' => true,
);

get_posts( $array );

$higher = [
	'numberposts'      => 1,
	'post_type'        => str_replace( [ 'single_', 'archive_' ], '', $section['id'] ),
	'fields'           => 'ids',
	'suppress_filters' => false,
];

$args = $higher;
$args['post_parent'] = $parent_page_id;
$args['fields'] = 'ids';
$args['suppress_filters'] = true;
$child_pages = get_posts( $args );
