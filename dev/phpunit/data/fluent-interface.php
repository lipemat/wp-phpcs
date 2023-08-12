<?php

use Lipe\Lib\Query\Args;

$args = new Args( [] );
$args->meta_value = 'fail';

$args = some_other_call();
$args->meta_value = 'pass';

$results = $wpdb->get_results( 'SELECT * FROM wp_post_meta' );
$using = $results[0]->meta_value;

$meta = get_post_meta_by_id( 1 );
if ( true === $meta->meta_value ) {
	return;
}

use Args\get_posts;

$another = (object) [
];

$args = new get_posts();
$args->meta_value = 'fail';
$args->orderby = $another->meta_value;

$args = new get_posts();
$args->orderby = $another->meta_value;

$clause = new Args\MetaQuery\Clause();
$clause->key = 'foo';
$clause->compare = 'EXISTS';
$args->meta_query = $clause;
