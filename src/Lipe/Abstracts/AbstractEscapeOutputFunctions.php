<?php
/**
 * Sniffs which check for escaping function with JS output.
 *
 * @package wp-phpcs\Lipe
 */

namespace Lipe\Abstracts;

use WordPressCS\WordPress\Helpers\EscapingFunctionsTrait;
use WordPressCS\WordPress\Sniff;

/**
 * Support checking if a user value is escaped in JS files.
 *
 * @author Mat Lipe
 * @since  4.0.0
 */
abstract class AbstractEscapeOutputFunctions extends Sniff {
	use EscapingFunctionsTrait;

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var string[]
	 */
	public $supportedTokenizers = [ 'JS' ];


	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array<int|string>
	 */
	public function register(): array {
		return [
			T_STRING,
		];
	}


	/**
	 * Set the escaping functions when the class is constructed
	 * to prevent collisions with the other trait.
	 */
	public function __construct() {
		$this->escapingFunctions = [
			'sanitize' => true,
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
