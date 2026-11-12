<?php
/** @noinspection ALL */

use Lipe\Lib\Taxonomy\Get_Terms;

get_users( [
	'exclude' => [ 1, 2, 3 ],
] );

get_terms( [
	'exclude' => [ 1, 2, 3 ],
] );

wp_dropdown_categories( [
	'exclude' => [ 1, 2, 3 ],
] );

wp_list_categories( [
	'exclude' => [ 1, 2, 3 ],
] );

get_categories( [
	'exclude' => [ 1, 2, 3 ],
] );

get_tags( [
	'exclude' => [ 1, 2, 3 ],
] );

wp_tag_cloud( [
	'exclude' => [ 1, 2, 3 ],
] );

wp_dropdown_users( [
	'exclude' => [ 1, 2, 3 ],
] );

wp_list_authors( [
	'exclude' => [ 1, 2, 3 ],
] );

$args = new Get_Terms( [] );
$args->exclude = \array_map( '\intval', $selected ?? [] );
