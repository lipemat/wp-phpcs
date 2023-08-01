<?php

new WP_Query( [
	'orderby' => 'rand',
] );
new WP_Query( [
	'meta_value' => 'foo',
	'orderby'    => 'meta_value',
] );
new WP_Query( [
	'orderby' => 'meta_value_num',
] );

$args = new Args\WP_Query();
$args->orderby = 'meta_value';

$args = new Lipe\Lib\Query\Args();
$args->orderby = 'rand';

$what = new stdClass();
$what->orderby = 'meta_value_num';

$args = [
	'orderby' => 'rand',
];
