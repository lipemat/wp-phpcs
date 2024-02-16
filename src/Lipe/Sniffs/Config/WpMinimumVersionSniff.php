<?php
/**
 * `Lipe.Config.WpMinimumVersion` Sniff
 *
 * Detect issues with the phpcs.xml file which affect included rules and
 * vendor sniffs
 *
 * @package wp-phpcs\Lipe
 */

namespace Lipe\Sniffs\Config;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHPCSUtils\BackCompat\Helper;
use WordPressCS\WordPress\Helpers\MinimumWPVersionTrait;

/**
 * Detect non-float values set for the `minimum_wp_version` in the phpcs.xml file.
 *
 * Prevents silent issues with setting the patch version.
 *
 * @link https://github.com/WordPress/WordPress-Coding-Standards/wiki/Customizable-sniff-properties#various-sniffs-set-the-minimum-supported-wp-version
 *
 * The MinimumWPVersionTrait validates the version as float instead of using version_compare.
 * We must set a valid float.
 * @link https://github.com/WordPress/WordPress-Coding-Standards/blob/f985e0aaf45e964d14a73e698b70a9c759c491f0/WordPress/Helpers/MinimumWPVersionTrait.php#L112-L115
 */
class WpMinimumVersionSniff implements Sniff {
	use MinimumWPVersionTrait;

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var string[]
	 */
	public $supportedTokenizers = [ 'PHP' ];


	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @throws \LogicException If the minimum_wp_version value in the phpcs.xml file is not valid.
	 * @return array<string|int>
	 */
	public function register(): array {
		$config_version = Helper::getConfigData( 'minimum_wp_version' );
		if ( null !== $config_version ) {
			$this->minimum_wp_version = $config_version;

			$this->set_minimum_wp_version();
			if ( $config_version !== $this->minimum_wp_version ) {
				if ( 1 === preg_match( '`^\d+\.\d+\.\d+$`', $config_version ) ) {
					$parts = \explode( '.', $config_version );
					$majorMinor = \array_slice( $parts, 0, 2 );
					//phpcs:ignore WordPress.Security.EscapeOutput
					throw new \LogicException( \sprintf( 'The `minimum_wp_version` value in the phpcs.xml file must include a minor version only! Try %s instead.', \implode( '.', $majorMinor ) ) );
				}
				throw new \LogicException( 'The `minimum_wp_version` value in the phpcs.xml file is not valid!' );
			}
		}

		return [ \T_NONE ];
	}


	/**
	 * NO-Op. This sniff does not process anything.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token
	 *                        in the stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process( File $phpcsFile, $stackPtr ): void {
	}
}
