<?php

$w = filter_input( INPUT_ENV, 'w', FILTER_VALIDATE_INT );
$sv = filter_input( INPUT_SERVER, 's', FILTER_VALIDATE_INT );
$a = filter_input_array( INPUT_SERVER, 'a' );
$c = filter_input( INPUT_COOKIE, 'c', FILTER_VALIDATE_INT );
