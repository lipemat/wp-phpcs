<?php
/**
 * Lipe.JS.StringConcatSniff
 *
 * @package wp-phpcs\Lipe
 */

namespace Lipe\Sniffs\JS;

use Lipe\Traits\EscapeOutputFunctions;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Looks for HTML string concatenation.
 *
 * Using VIP sniff verbatim as the sniff does not care what
 * function is used to escape, just that a function is used.
 *
 * Here to keep the `Lipe.JS`sniffs fully inclusive.
 */
class StringConcatSniff extends \WordPressVIPMinimum\Sniffs\JS\StringConcatSniff {
}
