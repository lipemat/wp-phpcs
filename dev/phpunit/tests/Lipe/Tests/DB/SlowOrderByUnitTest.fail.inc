<?php

new WP_Query( [
	'orderby'        => 'rand',
	'posts_per_page' => 4,
] );

new WP_Query( [
	'post_type' => [ 'page', 'post' ],
	'orderby'   => 'meta_value',
] );

new WP_Query( [
	'orderby' => 'meta_value_num',
] );
