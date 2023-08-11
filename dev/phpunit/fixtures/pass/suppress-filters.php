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

use Lipe\Lib\Query\Get_Posts;

$args = new Get_Posts();
$args->orderby = 'whatever';

get_posts( $args->get_light_args() );

$args = new Get_Posts();
get_posts( $args->get_light_args() );
