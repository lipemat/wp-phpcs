<?php

$args = [
	'first'  => true,
	'second' => false,
];

$args['third'] = 3;
$args['not-usable'] = [
	'second' => 'layer',
];
$args['fourth'] = 4;

some_function( $args );
