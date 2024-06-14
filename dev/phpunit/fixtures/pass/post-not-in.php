<?php
/** @noinspection ALL */

use Lipe\Lib\Taxonomy\Get_Terms;

get_users( [
	'exclude' => [ 1, 2, 3 ],
] );

get_terms( [
	'exclude' => [ 1, 2, 3 ],
] );

$args = new Get_Terms( [] );
$args->exclude = \array_map( '\intval', $selected ?? [] );
