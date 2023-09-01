<?php
declare( strict_types=1 );

use Lipe\Traits\ArrayHelpers;
use Lipe\Traits\ObjectHelpers;
use Lipe\Traits\VariableHelpers;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\LocalFile;
use PHP_CodeSniffer\Fixer;
use PHP_CodeSniffer\Ruleset;
use PHPUnit\Framework\TestCase;

/**
 * @author Mat Lipe
 * @since  August 2023
 *
 */
abstract class HelpersAbstract extends TestCase {
	use ArrayHelpers;
	use ObjectHelpers;
	use VariableHelpers;

	public $tokens = [];


	protected function convert_file_to_tokens( $file, $path = __DIR__ . '/data/' ) : array {
		$this->phpcsFile = new LocalFile(
			$path . $file . '.php',
			new Ruleset( new Config() ),
			new Config()
		);
		$this->phpcsFile->parse();
		return $this->phpcsFile->getTokens();
	}


	protected function get_raw_tokens_file( $file ) : array {
		$this->phpcsFile = new class extends LocalFile {
			/**
			 * @noinspection MagicMethodsValidityInspection
			 * @noinspection PhpMissingParentConstructorInspection
			 */
			public function __construct() {
				$this->fixer = new Fixer();
			}
		};

		$data = include( __DIR__ . '/data/' . $file . '.php' );

		/** @noinspection PhpUnhandledExceptionInspection */
		set_private_property( $this->phpcsFile, 'tokens', $data );
		$this->phpcsFile->numTokens = count( $this->phpcsFile->getTokens() );

		return $this->phpcsFile->getTokens();
	}
}
