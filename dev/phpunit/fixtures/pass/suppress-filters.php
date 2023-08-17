<?php
/**
 * Disable PHPStorm code conversion for this file.
 *
 *
 * @author Mat Lipe
 * @since  August 2023
 *
 */

class WP_SEO_Settings {
	public function example_permalink( array $section ) {
		$post = get_posts( array(
			'numberposts'      => 1,
			'post_type'        => str_replace( [ 'single_', 'archive_' ], '', $section['id'] ),
			'fields'           => 'ids',
			'suppress_filters' => false,
		) );
	}
}

use Advanced_Sidebar_Menu\Cache;
use Advanced_Sidebar_Menu\Widget_Options\Category\Display_Posts;
use Lipe\Lib\Query\Get_Posts;

$args = new Get_Posts();
$args->orderby = 'whatever';

get_posts( $args->get_light_args() );

$args = new Get_Posts();
get_posts( $args->get_light_args() );

$_args = (array) apply_filters( 'advanced-sidebar-menu-pro/walker/display-posts/get-post-args', [
	'post_type'        => $this->instance[ Display_Posts::POST_TYPE ],
	'numberposts'      => $this->instance[ Display_Posts::LIMIT ],
	'orderby'          => 'title',
	'order'            => 'ASC',
	'suppress_filters' => false,
	'tax_query'        => [
		[
			'taxonomy'         => $term->taxonomy,
			'terms'            => $term->term_id,
			'include_children' => false,
		],
	],
] );
$children = get_posts( $_args );

$cache = Cache::instance();
$child_pages = $cache->get_child_pages( $this );
if ( false === $child_pages ) {
	$args = $this->args;
	$args['post_parent'] = $parent_page_id;
	$args['fields'] = 'ids';
	$args['suppress_filters'] = false;
	$child_pages = get_posts( $args );

	$cache->add_child_pages( $this, $child_pages );
}
