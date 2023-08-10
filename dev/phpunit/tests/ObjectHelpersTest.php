<?php
declare( strict_types=1 );

use Lipe\Traits\ArrayHelpers;
use Lipe\Traits\ObjectHelpers;
use Lipe\Traits\VariableHelpers;
use PHP_CodeSniffer\Files\LocalFile;
use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__, 3 ) . '/src/Lipe/traits/ObjectHelpers.php';
require_once dirname( __DIR__, 3 ) . '/src/Lipe/traits/ArrayHelpers.php';

/**
 * @author Mat Lipe
 * @since  August 2023
 *
 */
class ObjectHelpersTest extends TestCase {
	use ArrayHelpers;
	use ObjectHelpers;
	use VariableHelpers;

	/**
	 * @var array|array[]
	 */
	protected $tokens;


	public function test_get_assignment() {
		$this->tokens = $this->get_tokens( 'object-helpers-complex' );
		$this->assertEquals( 30, $this->get_variable_assignment( 40 ) );
		$this->assertEquals( 153, $this->get_variable_assignment( 194 ) );
		$this->assertEquals( 60, $this->get_variable_assignment( 81 ) );

		$this->tokens = $this->get_tokens( 'object-helpers-simple' );
		$this->assertEquals( 14, $this->get_variable_assignment( 38 ) );
	}


	public function test_get_assignment_no_assignment() {
		$this->tokens = $this->get_tokens( 'object-helpers-simple' );
		$this->assertFalse( $this->get_variable_assignment( 13 ) );
	}


	public function test_get_assigned_properties() {
		$this->tokens = $this->get_tokens( 'object-helpers-complex' );
		$this->assertEquals( [], $this->get_assigned_properties( 40 ) );
		$this->assertEquals( [
			'suppress_filters' => 166,
			'post__in'         => 176,
		], $this->get_assigned_properties( 194 ) );
		$this->assertEquals( [
			'suppress_filters' => 74,
		], $this->get_assigned_keys_from_variable( 81 ) );
		$this->assertEquals( [
			'suppress_filters' => 115,
			'post_type'        => 126,
		], $this->get_assigned_keys_from_variable( 133 ) );

		$this->tokens = $this->get_tokens( 'object-helpers-simple' );
		$this->assertEquals( [ 'suppress_filters' => 31 ], $this->get_assigned_properties( 38 ) );
	}


	private function get_tokens( $file ) : array {
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
	 */
	public function strip_quotes( $string ) {
		return preg_replace( '`^([\'"])(.*)\1$`Ds', '$2', $string );
	}
}
