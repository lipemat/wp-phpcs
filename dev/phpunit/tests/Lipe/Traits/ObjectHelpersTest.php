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


	public function test_detect_fluent_interface_usage(): void {
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


	public function test_get_variable_assignment(): void {
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


	public function test_get_class_name(): void {
		$this->tokens = $this->convert_file_to_tokens( 'fluent-interface' );
		$this->assertSame( 'Args', $this->get_class_name( 14 ) );
		$this->assertSame( 'Args', $this->get_class_name( 29 ) );

		$this->assertFalse( $this->get_class_name( 39 ) );
		$this->assertFalse( $this->get_class_name( 48 ) );

		$this->assertSame( 'get_posts', $this->get_class_name( 140 ) );
		$this->assertSame( 'get_posts', $this->get_class_name( 151 ) );
		$this->assertSame( 'get_posts', $this->get_class_name( 160 ) );
		$this->assertSame( 'get_posts', $this->get_class_name( 172 ) );

		$this->assertSame( 'Args', $this->get_class_name( 195 ) );
		$this->assertSame( 'Args', $this->get_class_name( 210 ) );
		$this->assertSame( 'Args', $this->get_class_name( 219 ) );
		$this->assertSame( 'Args', $this->get_class_name( 234 ) );

		$this->assertSame( 'get_posts', $this->get_class_name( 183 ) );
		$this->assertSame( 'get_posts', $this->get_class_name( 228 ) );
		$this->assertSame( 'get_posts', $this->get_class_name( 336 ) );

		$this->assertfalse( $this->get_class_name( 342 ) );
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


	public function test_get_value_from_prop(): void {
		$this->tokens = $this->convert_file_to_tokens( 'fluent-interface' );
		$this->assertSame( 'fail', $this->get_value_from_prop( 31 ) );
		$this->assertFalse( $this->get_value_from_prop( 89 ) );
		$this->assertSame( '$array_clause', $this->get_value_from_prop( 277 ) );
		$this->assertFalse( $this->get_value_from_prop( 325 ) );
		$this->assertSame( '$array_clause', $this->get_value_from_prop( 338 ) );
	}
}
