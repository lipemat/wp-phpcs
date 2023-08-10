<?php
declare( strict_types=1 );

use Lipe\Traits\ObjectHelpers;
use PHP_CodeSniffer\Files\LocalFile;
use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__, 3 ) . '/src/Lipe/traits/ObjectHelpers.php';

/**
 * @author Mat Lipe
 * @since  August 2023
 *
 */
class ObjectHelpersTest extends TestCase {
	use ObjectHelpers;

	/**
	 * @var array|array[]
	 */
	protected $tokens;


	public function test_get_assignment() {
		$this->tokens = $this->get_tokens();
		$this->assertEquals( 14, $this->get_variable_assignment( 38 ) );
	}


	public function test_get_assignment_no_assignment() {
		$this->tokens = $this->get_tokens();
		$this->assertFalse( $this->get_variable_assignment( 13 ) );
	}


	public function test_get_assigned_properties() {
		$this->tokens = $this->get_tokens();
		$this->assertEquals( [ 'suppress_filters' => 31 ], $this->get_assigned_properties( 38 ) );
	}


	private function get_tokens() : array {
		$this->phpcsFile = new class extends LocalFile {
			public function __construct() {
			}
		};

		set_private_property( $this->phpcsFile, 'tokens', [
			0  =>
				[
					'type'       => 'T_OPEN_TAG',
					'code'       => 379,
					'content'    => '<?php
',
					'line'       => 1,
					'column'     => 1,
					'length'     => 5,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			1  =>
				[
					'type'       => 'T_WHITESPACE',
					'code'       => 382,
					'content'    => '
',
					'line'       => 2,
					'column'     => 1,
					'length'     => 0,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			2  =>
				[
					'code'       => 353,
					'type'       => 'T_USE',
					'content'    => 'use',
					'line'       => 3,
					'column'     => 1,
					'length'     => 3,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			3  =>
				[
					'code'       => 382,
					'type'       => 'T_WHITESPACE',
					'content'    => ' ',
					'line'       => 3,
					'column'     => 4,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			4  =>
				[
					'content'    => 'Lipe',
					'code'       => 319,
					'type'       => 'T_STRING',
					'line'       => 3,
					'column'     => 5,
					'length'     => 4,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			5  =>
				[
					'code'       => 390,
					'type'       => 'T_NS_SEPARATOR',
					'content'    => '\\',
					'line'       => 3,
					'column'     => 9,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			6  =>
				[
					'content'    => 'Lib',
					'code'       => 319,
					'type'       => 'T_STRING',
					'line'       => 3,
					'column'     => 10,
					'length'     => 3,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			7  =>
				[
					'code'       => 390,
					'type'       => 'T_NS_SEPARATOR',
					'content'    => '\\',
					'line'       => 3,
					'column'     => 13,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			8  =>
				[
					'content'    => 'Query',
					'code'       => 319,
					'type'       => 'T_STRING',
					'line'       => 3,
					'column'     => 14,
					'length'     => 5,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			9  =>
				[
					'code'       => 390,
					'type'       => 'T_NS_SEPARATOR',
					'content'    => '\\',
					'line'       => 3,
					'column'     => 19,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			10 =>
				[
					'content'    => 'Get_Posts',
					'code'       => 319,
					'type'       => 'T_STRING',
					'line'       => 3,
					'column'     => 20,
					'length'     => 9,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			11 =>
				[
					'type'       => 'T_SEMICOLON',
					'code'       => 'PHPCS_T_SEMICOLON',
					'content'    => ';',
					'line'       => 3,
					'column'     => 29,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			12 =>
				[
					'type'       => 'T_WHITESPACE',
					'code'       => 382,
					'content'    => '
',
					'line'       => 3,
					'column'     => 30,
					'length'     => 0,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			13 =>
				[
					'type'       => 'T_WHITESPACE',
					'code'       => 382,
					'content'    => '
',
					'line'       => 4,
					'column'     => 1,
					'length'     => 0,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			14 =>
				[
					'code'       => 320,
					'type'       => 'T_VARIABLE',
					'content'    => '$args',
					'line'       => 5,
					'column'     => 1,
					'length'     => 5,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			15 =>
				[
					'code'       => 382,
					'type'       => 'T_WHITESPACE',
					'content'    => ' ',
					'line'       => 5,
					'column'     => 6,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			16 =>
				[
					'type'       => 'T_EQUAL',
					'code'       => 'PHPCS_T_EQUAL',
					'content'    => '=',
					'line'       => 5,
					'column'     => 7,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			17 =>
				[
					'code'       => 382,
					'type'       => 'T_WHITESPACE',
					'content'    => ' ',
					'line'       => 5,
					'column'     => 8,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			18 =>
				[
					'code'       => 305,
					'type'       => 'T_NEW',
					'content'    => 'new',
					'line'       => 5,
					'column'     => 9,
					'length'     => 3,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			19 =>
				[
					'code'       => 382,
					'type'       => 'T_WHITESPACE',
					'content'    => ' ',
					'line'       => 5,
					'column'     => 12,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			20 =>
				[
					'content'    => 'Get_Posts',
					'code'       => 319,
					'type'       => 'T_STRING',
					'line'       => 5,
					'column'     => 13,
					'length'     => 9,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			21 =>
				[
					'type'               => 'T_OPEN_PARENTHESIS',
					'code'               => 'PHPCS_T_OPEN_PARENTHESIS',
					'content'            => '(',
					'line'               => 5,
					'column'             => 22,
					'length'             => 1,
					'parenthesis_opener' => 21,
					'parenthesis_closer' => 22,
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			22 =>
				[
					'type'               => 'T_CLOSE_PARENTHESIS',
					'code'               => 'PHPCS_T_CLOSE_PARENTHESIS',
					'content'            => ')',
					'line'               => 5,
					'column'             => 23,
					'length'             => 1,
					'parenthesis_opener' => 21,
					'parenthesis_closer' => 22,
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			23 =>
				[
					'type'       => 'T_SEMICOLON',
					'code'       => 'PHPCS_T_SEMICOLON',
					'content'    => ';',
					'line'       => 5,
					'column'     => 24,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			24 =>
				[
					'type'       => 'T_WHITESPACE',
					'code'       => 382,
					'content'    => '
',
					'line'       => 5,
					'column'     => 25,
					'length'     => 0,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			25 =>
				[
					'code'       => 320,
					'type'       => 'T_VARIABLE',
					'content'    => '$args',
					'line'       => 6,
					'column'     => 1,
					'length'     => 5,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			26 =>
				[
					'code'       => 366,
					'type'       => 'T_OBJECT_OPERATOR',
					'content'    => '->',
					'line'       => 6,
					'column'     => 6,
					'length'     => 2,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			27 =>
				[
					'content'    => 'suppress_filters',
					'code'       => 319,
					'type'       => 'T_STRING',
					'line'       => 6,
					'column'     => 8,
					'length'     => 16,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			28 =>
				[
					'code'       => 382,
					'type'       => 'T_WHITESPACE',
					'content'    => ' ',
					'line'       => 6,
					'column'     => 24,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			29 =>
				[
					'type'       => 'T_EQUAL',
					'code'       => 'PHPCS_T_EQUAL',
					'content'    => '=',
					'line'       => 6,
					'column'     => 25,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			30 =>
				[
					'code'       => 382,
					'type'       => 'T_WHITESPACE',
					'content'    => ' ',
					'line'       => 6,
					'column'     => 26,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			31 =>
				[
					'type'       => 'T_TRUE',
					'code'       => 'PHPCS_T_TRUE',
					'content'    => 'true',
					'line'       => 6,
					'column'     => 27,
					'length'     => 4,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			32 =>
				[
					'type'       => 'T_SEMICOLON',
					'code'       => 'PHPCS_T_SEMICOLON',
					'content'    => ';',
					'line'       => 6,
					'column'     => 31,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			33 =>
				[
					'type'       => 'T_WHITESPACE',
					'code'       => 382,
					'content'    => '
',
					'line'       => 6,
					'column'     => 32,
					'length'     => 0,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			34 =>
				[
					'type'       => 'T_WHITESPACE',
					'code'       => 382,
					'content'    => '
',
					'line'       => 7,
					'column'     => 1,
					'length'     => 0,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			35 =>
				[
					'type'       => 'T_STRING',
					'code'       => 319,
					'content'    => 'get_posts',
					'line'       => 8,
					'column'     => 1,
					'length'     => 9,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			36 =>
				[
					'type'               => 'T_OPEN_PARENTHESIS',
					'code'               => 'PHPCS_T_OPEN_PARENTHESIS',
					'content'            => '(',
					'line'               => 8,
					'column'             => 10,
					'length'             => 1,
					'parenthesis_opener' => 36,
					'parenthesis_closer' => 40,
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			37 =>
				[
					'code'               => 382,
					'type'               => 'T_WHITESPACE',
					'content'            => ' ',
					'line'               => 8,
					'column'             => 11,
					'length'             => 1,
					'nested_parenthesis' =>
						[
							36 => 40,
						],
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			38 =>
				[
					'code'               => 320,
					'type'               => 'T_VARIABLE',
					'content'            => '$args',
					'line'               => 8,
					'column'             => 12,
					'length'             => 5,
					'nested_parenthesis' =>
						[
							36 => 40,
						],
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			39 =>
				[
					'code'               => 382,
					'type'               => 'T_WHITESPACE',
					'content'            => ' ',
					'line'               => 8,
					'column'             => 17,
					'length'             => 1,
					'nested_parenthesis' =>
						[
							36 => 40,
						],
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			40 =>
				[
					'type'               => 'T_CLOSE_PARENTHESIS',
					'code'               => 'PHPCS_T_CLOSE_PARENTHESIS',
					'content'            => ')',
					'line'               => 8,
					'column'             => 18,
					'length'             => 1,
					'parenthesis_opener' => 36,
					'parenthesis_closer' => 40,
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			41 =>
				[
					'type'       => 'T_SEMICOLON',
					'code'       => 'PHPCS_T_SEMICOLON',
					'content'    => ';',
					'line'       => 8,
					'column'     => 19,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			42 =>
				[
					'type'       => 'T_WHITESPACE',
					'code'       => 382,
					'content'    => '
',
					'line'       => 8,
					'column'     => 20,
					'length'     => 0,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			43 =>
				[
					'type'       => 'T_WHITESPACE',
					'code'       => 382,
					'content'    => '
',
					'line'       => 9,
					'column'     => 1,
					'length'     => 0,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			44 =>
				[
					'code'       => 320,
					'type'       => 'T_VARIABLE',
					'content'    => '$args',
					'line'       => 10,
					'column'     => 1,
					'length'     => 5,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			45 =>
				[
					'code'       => 382,
					'type'       => 'T_WHITESPACE',
					'content'    => ' ',
					'line'       => 10,
					'column'     => 6,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			46 =>
				[
					'type'       => 'T_EQUAL',
					'code'       => 'PHPCS_T_EQUAL',
					'content'    => '=',
					'line'       => 10,
					'column'     => 7,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			47 =>
				[
					'code'       => 382,
					'type'       => 'T_WHITESPACE',
					'content'    => ' ',
					'line'       => 10,
					'column'     => 8,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			48 =>
				[
					'code'       => 305,
					'type'       => 'T_NEW',
					'content'    => 'new',
					'line'       => 10,
					'column'     => 9,
					'length'     => 3,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			49 =>
				[
					'code'       => 382,
					'type'       => 'T_WHITESPACE',
					'content'    => ' ',
					'line'       => 10,
					'column'     => 12,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			50 =>
				[
					'content'    => 'Get_Posts',
					'code'       => 319,
					'type'       => 'T_STRING',
					'line'       => 10,
					'column'     => 13,
					'length'     => 9,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			51 =>
				[
					'type'               => 'T_OPEN_PARENTHESIS',
					'code'               => 'PHPCS_T_OPEN_PARENTHESIS',
					'content'            => '(',
					'line'               => 10,
					'column'             => 22,
					'length'             => 1,
					'parenthesis_opener' => 51,
					'parenthesis_closer' => 52,
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			52 =>
				[
					'type'               => 'T_CLOSE_PARENTHESIS',
					'code'               => 'PHPCS_T_CLOSE_PARENTHESIS',
					'content'            => ')',
					'line'               => 10,
					'column'             => 23,
					'length'             => 1,
					'parenthesis_opener' => 51,
					'parenthesis_closer' => 52,
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			53 =>
				[
					'type'       => 'T_SEMICOLON',
					'code'       => 'PHPCS_T_SEMICOLON',
					'content'    => ';',
					'line'       => 10,
					'column'     => 24,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			54 =>
				[
					'type'       => 'T_WHITESPACE',
					'code'       => 382,
					'content'    => '
',
					'line'       => 10,
					'column'     => 25,
					'length'     => 0,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			55 =>
				[
					'code'       => 320,
					'type'       => 'T_VARIABLE',
					'content'    => '$args',
					'line'       => 11,
					'column'     => 1,
					'length'     => 5,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			56 =>
				[
					'code'       => 366,
					'type'       => 'T_OBJECT_OPERATOR',
					'content'    => '->',
					'line'       => 11,
					'column'     => 6,
					'length'     => 2,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			57 =>
				[
					'content'    => 'suppress_filters',
					'code'       => 319,
					'type'       => 'T_STRING',
					'line'       => 11,
					'column'     => 8,
					'length'     => 16,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			58 =>
				[
					'code'       => 382,
					'type'       => 'T_WHITESPACE',
					'content'    => ' ',
					'line'       => 11,
					'column'     => 24,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			59 =>
				[
					'type'       => 'T_EQUAL',
					'code'       => 'PHPCS_T_EQUAL',
					'content'    => '=',
					'line'       => 11,
					'column'     => 25,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			60 =>
				[
					'code'       => 382,
					'type'       => 'T_WHITESPACE',
					'content'    => ' ',
					'line'       => 11,
					'column'     => 26,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			61 =>
				[
					'type'       => 'T_FALSE',
					'code'       => 'PHPCS_T_FALSE',
					'content'    => 'false',
					'line'       => 11,
					'column'     => 27,
					'length'     => 5,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			62 =>
				[
					'type'       => 'T_SEMICOLON',
					'code'       => 'PHPCS_T_SEMICOLON',
					'content'    => ';',
					'line'       => 11,
					'column'     => 32,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			63 =>
				[
					'type'       => 'T_WHITESPACE',
					'code'       => 382,
					'content'    => '
',
					'line'       => 11,
					'column'     => 33,
					'length'     => 0,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
		] );
		$this->phpcsFile->numTokens = count( $this->phpcsFile->getTokens() );

		return $this->phpcsFile->getTokens();
	}
}
