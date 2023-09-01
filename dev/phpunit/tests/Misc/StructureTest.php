<?php
declare( strict_types=1 );

namespace Misc;

use HelpersAbstract;

class StructureTest extends HelpersAbstract {

	public function test_array_legacy_syntax() : void {
		$tokens = $this->convert_file_to_tokens( 'suppress-filters', 'fixtures/pass/' );
		$this->assertEquals( T_ARRAY, $tokens[65]['code'], 'The array is being converted to short syntax which invalidates the test. Turn off auto conversion for array in PHPStorm.' );
	}
}
