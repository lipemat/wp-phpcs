<?php

use Lipe\Lib\Query\Get_Posts;

class Complex {
	private function get_items( array $args ) {
		get_posts( $args );
	}


	public function get_items_public( array $args, string $another ) {
		$args['suppress_filters'] = false;
		get_posts( $args );
	}


	private function more_items( array $args, string $another ) {
		$args['suppress_filters'] = true;
		$args['post_type'] = 'post';
		get_posts( $args );
	}


	private function get_items_with_filter( Get_Posts $args ) {
		$args->suppress_filters = true;
		$args->post__in = [ 1, 2, 3 ];

		get_posts( $args );
	}


	private function get_items_with_filter_2( Get_Posts $args ) {
		$args->suppress_filters = false;
		$args->post__in = [ 1, 2, 3 ];

		get_posts( $args );
	}


	private function get_items_with_filter_and_array() {
		$args = new Get_Posts();
		$args->suppress_filters = true;
		$args->post__in = [ 1, 2, 3 ];

		get_posts( $args );
	}
}

function outside_function() {
	$args = new Get_Posts();
	$args->suppress_filters = false;

	get_posts( $args );
}

function get_children( $args ) {
	$args['suppress_filters'] = true;
	$args['post_type'] = 'post';
	get_posts( $args );
}

function( $args ) {
	$args['suppress_filters'] = false;
	get_posts( $args );
};

$args = [
	'suppress_filters' => true,
	'post_type'        => 'post',
];

fn() => get_posts( $args );
