<?php

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

$args = [
	'post_type'        => 'page',
	'suppress_filters' => false,
];
get_post( $args );
