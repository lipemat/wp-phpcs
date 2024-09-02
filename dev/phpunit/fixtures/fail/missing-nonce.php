<?php
function to_verify() {
	$w = isset( $_GET['w'] ) ? filter_var( wp_unslash( $_GET['w'] ), FILTER_VALIDATE_INT ) : '';
	$i = filter_input( INPUT_GET, 'i', FILTER_VALIDATE_INT );
	$p = filter_input( INPUT_POST, 'p', FILTER_VALIDATE_INT );
	$a = filter_input_array( INPUT_GET, 'a' );
	$ap = filter_input_array( INPUT_POST, 'ap' );
	$h = filter_has_var( INPUT_GET, 'h' );
	$p = filter_input( INPUT_REQUEST, 'p', FILTER_VALIDATE_INT );
}
