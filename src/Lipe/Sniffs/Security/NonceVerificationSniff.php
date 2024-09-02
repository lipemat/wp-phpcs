<?php
/**
 * Lipe.Security.NonceVerification
 *
 * @package Lipe
 */

namespace Lipe\Sniffs\Security;

use PHP_CodeSniffer\Files\File;
use WordPressCS\WordPress\AbstractFunctionRestrictionsSniff;

/**
 * Expand the `WordPress.Security.NonceVerification` sniff to also require nonce
 * when using:
 * - `filter_input()`
 * - `filter_input_array()`
 * - `filter_has_var()`
 *
 * @notice Does not check for standard superglobals so keep `WordPress.Security.NonceVerification` enabled.
 *
 * @link   https://github.com/WordPress/WordPress-Coding-Standards/issues/2299
 *
 * @author Mat Lipe
 * @since  4.4.0
 *
 * @code   `Missing` - For `POST` superglobals.
 * @code   `Recommended` - For `GET` or `REQUEST` superglobals.
 */
class NonceVerificationSniff extends \WordPressCS\WordPress\Sniffs\Security\NonceVerificationSniff {
	/**
	 * Functions this sniff is looking for.
	 *
	 * @var array<string, bool>
	 */
	protected array $target_functions = [
		'filter_has_var'     => false,
		'filter_input'       => true,
		'filter_input_array' => true,
	];

	/**
	 * INPUT constants to check for.
	 *
	 * `true`
	 * - Results in an error.
	 * - Results in 'Missing' code.
	 *
	 * `false`
	 * - Results in 'Recommended' code.
	 * - Results in a warning.
	 *
	 * @var array<string, bool>
	 */
	protected array $inputs = [
		'INPUT_POST'    => true,
		'INPUT_GET'     => false,
		'INPUT_REQUEST' => false,
	];


	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array<int, int>
	 */
	public function register(): array {
		return [
			\T_STRING => \T_STRING,
		];
	}


	/**
	 * Overrides the parent to:
	 * - Check for a proper function call.
	 * - Point to the `INPUT_*` constant for checking superglobals.
	 *
	 * @param int $stackPtr - Current position in the stack.
	 *
	 * @return int|void
	 */
	public function process_token( $stackPtr ) {
		if ( ! isset( $this->target_functions[ $this->tokens[ $stackPtr ]['content'] ] ) ) {
			return;
		}

		if ( ! $this->is_target_function( $stackPtr ) ) {
			return;
		}

		$input_type = $this->phpcsFile->findNext( \T_STRING, ( $stackPtr + 2 ), null, false, null, true );
		if ( ! \is_int( $input_type ) || ! isset( $this->inputs[ $this->tokens[ $input_type ]['content'] ] ) ) {
			return;
		}

		$this->superglobals = $this->inputs;
		return parent::process_token( $input_type );
	}


	/**
	 * Verify is the current token is a function call.
	 *
	 * @see AbstractFunctionRestrictionsSniff::is_targetted_token()
	 *
	 * @param int $stackPtr The position of the current token in the stack.
	 *
	 * @return bool
	 */
	public function is_target_function( int $stackPtr ): bool {
		$functions = new class( $this->phpcsFile ) extends AbstractFunctionRestrictionsSniff {
			/**
			 * Set the `$phpcsFile` and `$tokens` properties in the way
			 * normally done by `Sniff::process()`.
			 *
			 * @param File $phpcsFile - The current file being scanned.
			 */
			public function __construct( File $phpcsFile ) {
				$this->phpcsFile = $phpcsFile;
				$this->tokens = $phpcsFile->getTokens();
			}


			/**
			 * Not applicable to the way we are using the abstract class.
			 *
			 * @return array{}
			 */
			public function getGroups(): array {
				return [];
			}
		};
		return $functions->is_targetted_token( $stackPtr );
	}
}
