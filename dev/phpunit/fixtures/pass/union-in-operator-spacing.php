<?php
/** @noinspection ALL */

use Lipe\Project\Shortcodes\Shared_Image;
use Lipe\Project\Shortcodes\Shared_Image\Image;

try {
	$caption = Shared_Image::in()->get_caption( Image::from( $attachment_id ), get_post()?->post_name ?? '' );
} catch ( \ValueError | \TypeError ) {
	$caption = '';
}
