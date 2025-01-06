<?php
declare( strict_types=1 );

namespace Lipe\Traits;

/**
 * @author Mat Lipe
 * @since  August 2023
 *
 */
class ArrayHelpersTest extends \HelpersAbstract {
	/**
	 * @var array|array[]
	 */
	public $tokens;


	public function test_get_assigned_keys_from_variables() {
		$this->tokens = $this->get_raw_tokens_file( 'array-helpers-simple' );
		$this->assertEquals( [
			'suppress_filters' => 21,
			'post_type'        => 13,
		], $this->get_assigned_keys_from_variable( 31 ) );

		$this->tokens = $this->convert_file_to_tokens( 'fluent-interface' );
		$this->assertEquals( [
			'key'     => 'foo',
			'compare' => 'EXISTS',
		], \array_map( function( $token ) {
			return $this->get_static_value_for_element( $token );
		}, $this->get_assigned_keys_from_variable( 342 ) ) );
	}


	public function test_get_array_access_values() {
		$this->tokens = $this->convert_file_to_tokens( 'array-helpers-array-access' );
		$this->assertEquals( [
			'first'      => 13,
			'fourth'     => 65,
			'not-usable' => 45,
			'second'     => 21,
			'third'      => 35,
		], $this->get_assigned_keys_from_variable( 72 ) );

		$this->tokens = $this->convert_file_to_tokens( 'array-helpers-array-opener' );
		$this->assertEquals( [
			'numberposts'      => 13,
			'post_type'        => 21,
			'fields'           => 89,
			'suppress_filters' => 99,
			'post_parent'      => 79,
		], $this->get_assigned_keys_from_variable( 109 ) );
	}


	public function test_get_array_opener() {
		$this->tokens = $this->convert_file_to_tokens( 'array-helpers-array-opener' );
		$this->assertEquals( 6, $this->get_array_opener( 109 ) );
		$this->assertEquals( 119, $this->get_array_opener( 143 ) );
	}


	public function test_get_static_value_for_element(): void {
		$this->tokens = $this->convert_file_to_tokens( 'array-helpers-array-access' );
		$this->assertEquals( 'true', $this->get_static_value_for_element( 13 ) );
		$this->assertEquals( 'false', $this->get_static_value_for_element( 21 ) );
		$this->assertEquals( 'layer', $this->get_static_value_for_element( 52 ) );
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
	 * return string|null
	 */
	public function strip_quotes( $string ) {
		return preg_replace( '`^([\'"])(.*)\1$`Ds', '$2', $string );
	}
}
