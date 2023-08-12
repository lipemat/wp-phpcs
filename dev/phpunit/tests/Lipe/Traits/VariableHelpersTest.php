<?php
declare( strict_types=1 );

namespace Lipe\Traits;

use PHPUnit\Framework\TestCase;

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

}
