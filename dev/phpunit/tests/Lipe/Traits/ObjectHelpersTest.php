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


	public function test_get_assignment() {
		$this->tokens = $this->get_raw_tokens_file( 'object-helpers-complex' );
		$this->assertEquals( 30, $this->get_variable_assignment( 40 ) );
		$this->assertEquals( 153, $this->get_variable_assignment( 194 ) );
		$this->assertEquals( 60, $this->get_variable_assignment( 81 ) );

		$this->tokens = $this->get_raw_tokens_file( 'object-helpers-simple' );
		$this->assertEquals( 14, $this->get_variable_assignment( 38 ) );
	}


	public function test_get_assignment_no_assignment() {
		$this->tokens = $this->get_raw_tokens_file( 'object-helpers-simple' );
		$this->assertFalse( $this->get_variable_assignment( 13 ) );
	}


	public function test_get_assigned_properties() {
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
