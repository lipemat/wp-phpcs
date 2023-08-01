<?php

new WP_Query( [
	'orderby'        => 'ID',
	'posts_per_page' => 4,
] );

new WP_Query( [
	'post_type' => [ 'page', 'post' ],
	'orderby'   => 'title',
] );

new WP_Query( [
	'orderby' => 'date',
] );
