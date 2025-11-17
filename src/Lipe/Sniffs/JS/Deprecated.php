<?php
/**
 * Lipe.JS.Deprecated
 *
 * @package Lipe
 */

namespace Lipe\Sniffs\JS;

use WordPressCS\WordPress\Sniff;

/**
 * Placeholder class to prevent failing PHPCS configuration using
 * the `Lipe.JS` namespace.
 *
 * Version 4 of PHPCS will no longer support JavaScript sniffing.
 *
 * @todo       Remove in version 5.
 *
 * @deprecated In favor of using ESLint for JS linting.
 */
class Deprecated extends Sniff {
	/**
	 * Do Nothing
	 *
	 * @param int $stackPtr - Current position in the stack.
	 *
	 * @return void
	 */
	public function process_token( $stackPtr ): void {
	}


	/**
	 * Register the tokens that this sniff wants to listen for.
	 *
	 * @return array|int[]|string[]
	 */
	public function register(): array {
		return [];
	}
}
