<?php

class Parsable {
	private array $args = [
		'post_type'        => 'page',
		'suppress_filters' => false,
	];


	public function get_args() {
		return get_posts( $this->args );
	}


	public $other = [
		'post_type'        => 'page',
		'suppress_filters' => false,
	];


	public function get_other() {
		return get_posts( $this->other );
	}


	public $local = [
		'post_type'        => 'page',
		'suppress_filters' => true,
	];


	public function get_other_local() {
		return get_posts( $this->local );
	}


	public $different = [];
}

class UnableToParse {
	public function get_other() {
		return get_posts( $this->local );
	}
}
