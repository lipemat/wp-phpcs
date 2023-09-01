<?php
/**
 * AbstractArrayObjectAssignment sniff.
 *
 * @package Lipe
 */

namespace Lipe\Abstracts;

use Lipe\Traits\ObjectHelpers;
use Lipe\Traits\VariableHelpers;
use PHPCSUtils\Utils\MessageHelper;
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
	 * @return array<int|string>
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
	public function process_token( $stackPtr ) : void {
		$this->stackPtr = $stackPtr;
		parent::process_token( $stackPtr );

		// Check for a fluent interface using the parameters.
		if ( $this->is_object_assignment( $stackPtr ) ) {
			$prop = $this->phpcsFile->findNext( \T_OPEN_CURLY_BRACKET, ( $stackPtr + 1 ), null, true, null, true );
			if ( false === $prop ) {
				return;
			}
			foreach ( $this->groups_cache as $groupName => $group ) {
				foreach ( $group['keys'] as $occurrence ) {
					if ( $this->tokens[ $prop ]['content'] === $occurrence ) {
						$message = $group ['message'];
						MessageHelper::addMessage( $this->phpcsFile, $message, $prop, ( 'error' === $group['type'] ), MessageHelper::stringToErrorcode( $groupName . '_' . $occurrence ) );
					}
				}
			}
		}

		unset( $this->stackPtr );
	}
}
