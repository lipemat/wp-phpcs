<?php
declare( strict_types=1 );

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
 *
 */
class Deprecated extends Sniff {
	public function process_token( $stackPtr ): void {
	}


	public function register(): array {
		return [];
	}
}
