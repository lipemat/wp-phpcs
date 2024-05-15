<?php
/** @noinspection ALL */
/**
 * Disable PHPStorm code conversion for this file.
 *
 *
 * @author Mat Lipe
 * @since  August 2023
 *
 */
$w = isset( $v )&&isset( $q );
try {
	$caption = Shared_Image::in()->get_caption( Image::from( $attachment_id ), get_post()?->post_name ?? '' );
} catch ( \ValueError | \TypeError ) {
	$caption = '';
}

$v = $w??$q;
