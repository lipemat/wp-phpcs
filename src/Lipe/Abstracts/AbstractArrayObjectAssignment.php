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
	 * @var ?int
	 */
	protected $stackPtr;

	/**
	 * Pre-built lookup `[ key => [ group_name => group ] ]` built lazily from
	 * `$groups_cache` so the per-token hot path is an `isset()` on the property
	 * name rather than a nested foreach over groups × keys.
	 *
	 * @var array<string, array<string, array<string, mixed>>>
	 */
	private array $key_to_groups;


	/**
	 * Include object operators in the list of tokens to check.
	 *
	 * Adds support for checking fluent interfaces such as:
	 * - johnbillion/args
	 * - lipemat/wp-libs
	 *
	 * @return array<int|string>
	 */
	public function register(): array {
		$tokens = parent::register();
		$tokens[] = T_OBJECT_OPERATOR;
		return $tokens;
	}


	/**
	 * Overrides the parent to store the stackPtr for later use.
	 *
	 * @param int $stackPtr - Current position in the stack.
	 */
	public function process_token( $stackPtr ): void {
		$this->stackPtr = $stackPtr;
		parent::process_token( $stackPtr );

		// Check for a fluent interface using the parameters.
		if ( $this->is_object_assignment( $stackPtr ) ) {
			$prop = $this->phpcsFile->findNext( \T_OPEN_CURLY_BRACKET, ( $stackPtr + 1 ), null, true, null, true );
			if ( false === $prop ) {
				$this->stackPtr = null;
				return;
			}

			$prop_content = $this->tokens[ $prop ]['content'];
			$matches = $this->get_groups_by_key( $prop_content );
			if ( [] !== $matches ) {
				$value = $this->get_value_from_prop( $prop );
				$line = $this->tokens[ $prop ]['line'];
				foreach ( $matches as $groupName => $group ) {
					$output = $this->callback( $prop_content, $value, $line, $group );
					if ( ! isset( $output ) || false === $output ) {
						continue;
					}
					$message = ( true === $output ) ? $group['message'] : $output;
					MessageHelper::addMessage( $this->phpcsFile, $message, $prop, ( 'error' === $group['type'] ), MessageHelper::stringToErrorcode( $groupName . '_' . $prop_content ) );
				}
			}
		}

		$this->stackPtr = null;
	}


	/**
	 * Build (once per ruleset) and return the groups that target the given key.
	 *
	 * @param string $key - The property/key name from the token.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function get_groups_by_key( string $key ): array {
		if ( ! isset( $this->key_to_groups ) ) {
			$this->key_to_groups = [];
			foreach ( $this->groups_cache as $groupName => $group ) {
				foreach ( $group['keys'] as $occurrence ) {
					$this->key_to_groups[ $occurrence ][ $groupName ] = $group;
				}
			}
		}
		return $this->key_to_groups[ $key ] ?? [];
	}
}
