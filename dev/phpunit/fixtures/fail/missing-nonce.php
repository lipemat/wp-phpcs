<?php
function to_verify() {
	$w = filter_has_var( INPUT_POST, 'w' );

	$p = filter_input( INPUT_POST, 'p', FILTER_VALIDATE_INT );

	$ap = filter_input_array( INPUT_POST, 'ap' );

	$p = filter_input( INPUT_REQUEST, 'p', FILTER_VALIDATE_INT );

	$r = filter_has_var( INPUT_REQUEST, 'r' );
}
