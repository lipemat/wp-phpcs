<?php
/**
 * Trait used by escape sniffs.
 *
 * @package wp-phpcs\Lipe
 */

namespace Lipe\Traits;

use WordPressCS\WordPress\Helpers\EscapingFunctionsTrait;

trait EscapeOutputFunctions {
	use EscapingFunctionsTrait;

	/**
	 * Set the escaping functions when the class is constructed
	 * to prevent collisions with the other trait.
	 */
	public function __construct() {
		$this->escapingFunctions = [
			'sanitize'  => true,
			'dompurify' => true,
		];
	}


	/**
	 * Detect if current position is an escape function
	 *
	 * @param int|false $functionToken token amount scape function.
	 *
	 * @return bool
	 */
	protected function isEscapeFunction( $functionToken ): bool {
		if ( false === $functionToken ) {
			return false;
		}
		return $this->is_escaping_function( $this->tokens[ $functionToken ]['content'] );
	}
}
