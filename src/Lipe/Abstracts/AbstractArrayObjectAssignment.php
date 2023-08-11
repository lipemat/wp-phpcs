<?php
/**
 * AbstractArrayObjectAssignment sniff.
 *
 * @package Lipe
 */

namespace Lipe\Abstracts;

use Lipe\Traits\ObjectHelpers;
use Lipe\Traits\VariableHelpers;
use WordPressCS\WordPress\AbstractArrayAssignmentRestrictionsSniff;

/**
 * Expand the array assignment restrictions sniff to include object operators.
 *
 * - Support for fluent interfaces.
 * - Support for object operators.
 * - Simplifies the returned code for warnings and errors.
 *
 * @author Mat Lipe
 * @since  3.1.0
 */
abstract class AbstractArrayObjectAssignment extends AbstractArrayAssignmentRestrictionsSniff {
	use VariableHelpers;
	use ObjectHelpers;

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var string[]
	 */
	public $supportedTokenizers = [ 'PHP' ];

	/**
	 * The current stack pointer.
	 *
	 * @var int
	 */
	protected $stackPtr;


	/**
	 * Include object operators in the list of tokens to check.
	 *
	 * Adds support for checking fluent interfaces such as:
	 * - johnbillion/args
	 * - lipemat/wp-libs
	 *
	 * @return array
	 */
	public function register() : array {
		$tokens = parent::register();
		$tokens[] = T_OBJECT_OPERATOR;
		return $tokens;
	}


	/**
	 * Process a token.
	 *
	 * Overrides the parent to store the stackPtr for later use.
	 *
	 * @param int $stackPtr - Current position in the stack.
	 */
	public function process_token( $stackPtr ) {
		$this->stackPtr = $stackPtr;
		parent::process_token( $stackPtr );

		// Check for a fluent interface using the parameters.
		if ( $this->is_object_assignment( $stackPtr ) ) {
			$prop = $this->phpcsFile->findNext( \T_OPEN_CURLY_BRACKET, ( $stackPtr + 1 ), null, true );
			foreach ( $this->groups_cache as $groupName => $group ) {
				foreach ( $group['keys'] as $occurrence ) {
					if ( $this->tokens[ $prop ]['content'] === $occurrence ) {
						$message = $group ['message'];
						$this->addMessage( $message, $prop, ( 'error' === $group['type'] ), $this->string_to_errorcode( $groupName . '_' . $occurrence ) );
					}
				}
			}
		}

		unset( $this->stackPtr );
	}


	/**
	 * Simplify the error code to just the code and not the group.
	 *
	 * @param string $base_string - String provide by parent class with the group included.
	 *
	 * @return array|null|string|string[]
	 */
	protected function string_to_errorcode( $base_string ) {
		return preg_replace( '/.+?_/', '', $base_string, 1 );
	}
}
