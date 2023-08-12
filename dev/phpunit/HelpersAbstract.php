<?php
declare( strict_types=1 );

use Lipe\Traits\ArrayHelpers;
use Lipe\Traits\ObjectHelpers;
use Lipe\Traits\VariableHelpers;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\LocalFile;
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
			public function __construct() {
			}
		};

		$data = include( __DIR__ . '/data/' . $file . '.php' );

		set_private_property( $this->phpcsFile, 'tokens', $data );
		$this->phpcsFile->numTokens = count( $this->phpcsFile->getTokens() );

		return $this->phpcsFile->getTokens();
	}


	/**
	 * Copied verbatim from the WordPress-Core sniff.
	 *
	 * For mocking purposes.
	 *
	 */
	protected function find_array_open_close( $stackPtr ) {
		/*
		 * Determine the array opener & closer.
		 */
		if ( \T_ARRAY === $this->tokens[ $stackPtr ]['code'] ) {
			if ( isset( $this->tokens[ $stackPtr ]['parenthesis_opener'] ) ) {
				$opener = $this->tokens[ $stackPtr ]['parenthesis_opener'];

				if ( isset( $this->tokens[ $opener ]['parenthesis_closer'] ) ) {
					$closer = $this->tokens[ $opener ]['parenthesis_closer'];
				}
			}
		} else {
			// Short array syntax.
			$opener = $stackPtr;
			$closer = $this->tokens[ $stackPtr ]['bracket_closer'];
		}

		if ( isset( $opener, $closer ) ) {
			return [
				'opener' => $opener,
				'closer' => $closer,
			];
		}

		return false;
	}


	/**
	 * Copied verbatim from the WordPress-Core sniff.
	 *
	 * For mocking purposes.
	 *
	 * @return string|null
	 */
	public function strip_quotes( $string ) {
		return preg_replace( '`^([\'"])(.*)\1$`Ds', '$2', $string );
	}
}
