<?php
declare( strict_types=1 );

namespace Lipe\Traits;

/**
 * @author Mat Lipe
 * @since  August 2023
 *
 */
class VariableHelpersTest extends \HelpersAbstract {
	public function test_get_value_for_element() {
		$this->tokens = $this->convert_file_to_tokens( 'fluent-interface' );
		$this->assertEquals( 'EXISTS', $this->get_static_value_from_variable( 329, 'first' ) );
	}


	public function test_get_class_property() {
		$this->tokens = $this->convert_file_to_tokens( 'variable-helpers-class-property' );

		$this->assertFalse( $this->get_class_property( 227 ) );

		$this->assertEquals( 13, $this->get_class_property( 58 ) );
		$this->assertFalse( $this->get_class_property( 60 ) );
		$this->assertEquals( 73, $this->get_class_property( 118 ) );
		$this->assertEquals( 133, $this->get_class_property( 178 ) );

		$this->assertEquals( [
			'post_type'        => 144,
			'suppress_filters' => 152,
		], $this->get_assigned_keys_from_variable( 178 ) );
		$this->assertEquals( T_TRUE, $this->tokens[ $this->get_assigned_keys_from_variable( 178 )['suppress_filters'] ]['code'] );
	}


	public function test_get_start_of_class() {
		$this->tokens = $this->convert_file_to_tokens( 'variable-helpers-class-property' );

		$this->assertEquals( 2, $this->get_start_of_class( 58 ) );
		$this->assertEquals( 2, $this->get_start_of_class( 118 ) );
		$this->assertEquals( 2, $this->get_start_of_class( 178 ) );
		$this->assertEquals( 2, $this->get_start_of_class( 178 ) );

		$this->assertEquals( 204, $this->get_start_of_class( 227 ) );
	}


	public function test_is_scalar(): void {
		$this->tokens = $this->convert_file_to_tokens( 'variable-helpers-class-property' );
		$this->assertTrue( $this->is_scalar( 32 ) );

		$this->assertTrue( $this->is_scalar( 144 ) );
		$this->assertTrue( $this->is_scalar( 152 ) );
		$this->assertFalse( $this->is_scalar( 178 ) );
		$this->assertFalse( $this->is_scalar( 204 ) );
		$this->assertTrue( $this->is_scalar( 308 ) );
		$this->assertFalse( $this->is_scalar( 297 ) );
		$this->assertTrue( $this->is_scalar( 286 ) );
		$this->assertTrue( $this->is_scalar( 275 ) );
		$this->assertTrue( $this->is_scalar( 264 ) );
		$this->assertTrue( $this->is_scalar( 253 ) );
	}

}
