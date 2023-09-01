<?php
declare( strict_types=1 );

namespace Lipe\Traits;

require_once dirname( __DIR__, 5 ) . '/src/Lipe/traits/ObjectHelpers.php';
require_once dirname( __DIR__, 5 ) . '/src/Lipe/traits/ArrayHelpers.php';

/**
 * @author Mat Lipe
 * @since  August 2023
 *
 */
class ObjectHelpersTest extends \HelpersAbstract {
	use ObjectHelpers;

	/**
	 * @var array
	 */
	public $tokens;


	public function test_detect_fluent_interface_usage() {
		$this->tokens = $this->convert_file_to_tokens( 'fluent-interface' );
		$this->assertEquals( 14, $this->get_variable_assignment( 29 ) );
		$this->assertEquals( 39, $this->get_variable_assignment( 48 ) );

		$this->assertTrue( $this->is_object_assignment( 29 ) );
		$this->assertTrue( $this->is_object_assignment( 30 ) );
		$this->assertTrue( $this->is_object_assignment( 161 ) );

		$this->assertFalse( $this->is_object_assignment( 32 ) ); // random.
		$this->assertFalse( $this->is_object_assignment( 48 ) );
		$this->assertFalse( $this->is_object_assignment( 49 ) );
		$this->assertFalse( $this->is_object_assignment( 76 ) );
		$this->assertFalse( $this->is_object_assignment( 105 ) );
		$this->assertFalse( $this->is_object_assignment( 167 ) );
	}


	public function test_get_variable_assignment() {
		$this->tokens = $this->get_raw_tokens_file( 'object-helpers-complex' );
		$this->assertEquals( 30, $this->get_variable_assignment( 40 ) );
		$this->assertEquals( 153, $this->get_variable_assignment( 194 ) );
		$this->assertEquals( 60, $this->get_variable_assignment( 81 ) );

		$this->tokens = $this->get_raw_tokens_file( 'object-helpers-simple' );
		$this->assertEquals( 14, $this->get_variable_assignment( 38 ) );

		$this->tokens = $this->convert_file_to_tokens( 'fluent-interface' );
		$this->assertEquals( 14, $this->get_variable_assignment( 14 ) );
		$this->assertEquals( 14, $this->get_variable_assignment( 29 ) );

		$this->assertEquals( 39, $this->get_variable_assignment( 39 ) );
		$this->assertEquals( 39, $this->get_variable_assignment( 48 ) );

		$this->assertEquals( 140, $this->get_variable_assignment( 140 ) );
		$this->assertEquals( 140, $this->get_variable_assignment( 151 ) );
		$this->assertEquals( 140, $this->get_variable_assignment( 160 ) );

		$this->assertEquals( 172, $this->get_variable_assignment( 172 ) );
		$this->assertEquals( 172, $this->get_variable_assignment( 183 ) );
		$this->assertEquals( 172, $this->get_variable_assignment( 228 ) );

		$this->assertEquals( 195, $this->get_variable_assignment( 195 ) );
		$this->assertEquals( 195, $this->get_variable_assignment( 210 ) );
		$this->assertEquals( 195, $this->get_variable_assignment( 219 ) );
		$this->assertEquals( 195, $this->get_variable_assignment( 234 ) );
	}


	public function test_is_class_object() {
		$this->tokens = $this->convert_file_to_tokens( 'fluent-interface' );
		$this->assertTrue( $this->is_class_object( 14 ) );
		$this->assertTrue( $this->is_class_object( 29 ) );

		$this->assertFalse( $this->is_class_object( 39 ) );
		$this->assertFalse( $this->is_class_object( 48 ) );

		$this->assertTrue( $this->is_class_object( 140 ) );
		$this->assertTrue( $this->is_class_object( 151 ) );
		$this->assertTrue( $this->is_class_object( 160 ) );
		$this->assertTrue( $this->is_class_object( 172 ) );

		$this->assertTrue( $this->is_class_object( 195 ) );
		$this->assertTrue( $this->is_class_object( 210 ) );
		$this->assertTrue( $this->is_class_object( 219 ) );
		$this->assertTrue( $this->is_class_object( 234 ) );

		$this->assertTrue( $this->is_class_object( 183 ) );
		$this->assertTrue( $this->is_class_object( 228 ) );
		$this->assertTrue( $this->is_class_object( 336 ) );

		$this->assertfalse( $this->is_class_object( 342 ) );
	}


	public function test_get_assignment_no_assignment(): void {
		$this->tokens = $this->get_raw_tokens_file( 'object-helpers-simple' );
		$this->assertFalse( $this->get_variable_assignment( 13 ) );
	}


	public function test_get_assigned_properties(): void {
		$this->tokens = $this->get_raw_tokens_file( 'object-helpers-complex' );
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

		$this->tokens = $this->get_raw_tokens_file( 'object-helpers-simple' );
		$this->assertEquals( [ 'suppress_filters' => 31 ], $this->get_assigned_properties( 38 ) );
	}
}
