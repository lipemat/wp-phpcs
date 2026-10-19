<?php

$w = filter_input( INPUT_ENV, 'w', FILTER_VALIDATE_INT );
$sv = filter_input( INPUT_SERVER, 's', FILTER_VALIDATE_INT );
$a = filter_input_array( INPUT_SERVER, 'a' );
$c = filter_input( INPUT_COOKIE, 'c', FILTER_VALIDATE_INT );
$e = filter_input( INPUT_GET, 'e', FILTER_VALIDATE_INT );
$a = filter_input_array( INPUT_GET, 'a' );
$w = isset( $_GET['w'] ) ? filter_var( wp_unslash( $_GET['w'] ), FILTER_VALIDATE_INT ) : '';
$r = filter_has_var( INPUT_GET, 'r' );
