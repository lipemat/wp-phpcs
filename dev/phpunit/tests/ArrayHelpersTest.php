<?php
declare( strict_types=1 );

use PHP_CodeSniffer\Files\LocalFile;

/**
 * @author Mat Lipe
 * @since  August 2023
 *
 */
class ArrayHelpersTest extends HelpersAbstract {
	/**
	 * @var array|array[]
	 */
	protected $tokens;


	public function test_get_assigned_keys_from_variables() {
		$this->tokens = $this->get_tokens();
		$this->assertEquals( [
			'suppress_filters' => 21,
			'post_type'        => 13,
		], $this->get_assigned_keys_from_variable( 31 ) );
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
					'code'       => 320,
					'type'       => 'T_VARIABLE',
					'content'    => '$array',
					'line'       => 3,
					'column'     => 1,
					'length'     => 6,
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
					'column'     => 7,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			4  =>
				[
					'type'       => 'T_EQUAL',
					'code'       => 'PHPCS_T_EQUAL',
					'content'    => '=',
					'line'       => 3,
					'column'     => 8,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			5  =>
				[
					'code'       => 382,
					'type'       => 'T_WHITESPACE',
					'content'    => ' ',
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
					'type'           => 'T_OPEN_SHORT_ARRAY',
					'code'           => 'PHPCS_T_OPEN_SHORT_ARRAY',
					'content'        => '[',
					'line'           => 3,
					'column'         => 10,
					'length'         => 1,
					'bracket_opener' => 6,
					'bracket_closer' => 24,
					'level'          => 0,
					'conditions'     =>
						[
						],
				],
			7  =>
				[
					'type'       => 'T_WHITESPACE',
					'code'       => 382,
					'content'    => '
',
					'line'       => 3,
					'column'     => 11,
					'length'     => 0,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			8  =>
				[
					'type'         => 'T_WHITESPACE',
					'code'         => 382,
					'content'      => '    ',
					'line'         => 4,
					'column'       => 1,
					'orig_content' => '	',
					'length'       => 4,
					'level'        => 0,
					'conditions'   =>
						[
						],
				],
			9  =>
				[
					'code'       => 323,
					'type'       => 'T_CONSTANT_ENCAPSED_STRING',
					'content'    => '\'post_type\'',
					'line'       => 4,
					'column'     => 5,
					'length'     => 11,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			10 =>
				[
					'code'       => 382,
					'type'       => 'T_WHITESPACE',
					'content'    => '        ',
					'line'       => 4,
					'column'     => 16,
					'length'     => 8,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			11 =>
				[
					'code'       => 268,
					'type'       => 'T_DOUBLE_ARROW',
					'content'    => '=>',
					'line'       => 4,
					'column'     => 24,
					'length'     => 2,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			12 =>
				[
					'code'       => 382,
					'type'       => 'T_WHITESPACE',
					'content'    => ' ',
					'line'       => 4,
					'column'     => 26,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			13 =>
				[
					'code'       => 323,
					'type'       => 'T_CONSTANT_ENCAPSED_STRING',
					'content'    => '\'page\'',
					'line'       => 4,
					'column'     => 27,
					'length'     => 6,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			14 =>
				[
					'type'       => 'T_COMMA',
					'code'       => 'PHPCS_T_COMMA',
					'content'    => ',',
					'line'       => 4,
					'column'     => 33,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			15 =>
				[
					'type'       => 'T_WHITESPACE',
					'code'       => 382,
					'content'    => '
',
					'line'       => 4,
					'column'     => 34,
					'length'     => 0,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			16 =>
				[
					'type'         => 'T_WHITESPACE',
					'code'         => 382,
					'content'      => '    ',
					'line'         => 5,
					'column'       => 1,
					'orig_content' => '	',
					'length'       => 4,
					'level'        => 0,
					'conditions'   =>
						[
						],
				],
			17 =>
				[
					'code'       => 323,
					'type'       => 'T_CONSTANT_ENCAPSED_STRING',
					'content'    => '\'suppress_filters\'',
					'line'       => 5,
					'column'     => 5,
					'length'     => 18,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			18 =>
				[
					'code'       => 382,
					'type'       => 'T_WHITESPACE',
					'content'    => ' ',
					'line'       => 5,
					'column'     => 23,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			19 =>
				[
					'code'       => 268,
					'type'       => 'T_DOUBLE_ARROW',
					'content'    => '=>',
					'line'       => 5,
					'column'     => 24,
					'length'     => 2,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			20 =>
				[
					'code'       => 382,
					'type'       => 'T_WHITESPACE',
					'content'    => ' ',
					'line'       => 5,
					'column'     => 26,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			21 =>
				[
					'type'       => 'T_TRUE',
					'code'       => 'PHPCS_T_TRUE',
					'content'    => 'true',
					'line'       => 5,
					'column'     => 27,
					'length'     => 4,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			22 =>
				[
					'type'       => 'T_COMMA',
					'code'       => 'PHPCS_T_COMMA',
					'content'    => ',',
					'line'       => 5,
					'column'     => 31,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			23 =>
				[
					'type'       => 'T_WHITESPACE',
					'code'       => 382,
					'content'    => '
',
					'line'       => 5,
					'column'     => 32,
					'length'     => 0,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			24 =>
				[
					'type'           => 'T_CLOSE_SHORT_ARRAY',
					'code'           => 'PHPCS_T_CLOSE_SHORT_ARRAY',
					'content'        => ']',
					'line'           => 6,
					'column'         => 1,
					'length'         => 1,
					'bracket_opener' => 6,
					'bracket_closer' => 24,
					'level'          => 0,
					'conditions'     =>
						[
						],
				],
			25 =>
				[
					'type'       => 'T_SEMICOLON',
					'code'       => 'PHPCS_T_SEMICOLON',
					'content'    => ';',
					'line'       => 6,
					'column'     => 2,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			26 =>
				[
					'type'       => 'T_WHITESPACE',
					'code'       => 382,
					'content'    => '
',
					'line'       => 6,
					'column'     => 3,
					'length'     => 0,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			27 =>
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
			28 =>
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
			29 =>
				[
					'type'               => 'T_OPEN_PARENTHESIS',
					'code'               => 'PHPCS_T_OPEN_PARENTHESIS',
					'content'            => '(',
					'line'               => 8,
					'column'             => 10,
					'length'             => 1,
					'parenthesis_opener' => 29,
					'parenthesis_closer' => 33,
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			30 =>
				[
					'code'               => 382,
					'type'               => 'T_WHITESPACE',
					'content'            => ' ',
					'line'               => 8,
					'column'             => 11,
					'length'             => 1,
					'nested_parenthesis' =>
						[
							29 => 33,
						],
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			31 =>
				[
					'code'               => 320,
					'type'               => 'T_VARIABLE',
					'content'            => '$array',
					'line'               => 8,
					'column'             => 12,
					'length'             => 6,
					'nested_parenthesis' =>
						[
							29 => 33,
						],
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			32 =>
				[
					'code'               => 382,
					'type'               => 'T_WHITESPACE',
					'content'            => ' ',
					'line'               => 8,
					'column'             => 18,
					'length'             => 1,
					'nested_parenthesis' =>
						[
							29 => 33,
						],
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			33 =>
				[
					'type'               => 'T_CLOSE_PARENTHESIS',
					'code'               => 'PHPCS_T_CLOSE_PARENTHESIS',
					'content'            => ')',
					'line'               => 8,
					'column'             => 19,
					'length'             => 1,
					'parenthesis_opener' => 29,
					'parenthesis_closer' => 33,
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			34 =>
				[
					'type'       => 'T_SEMICOLON',
					'code'       => 'PHPCS_T_SEMICOLON',
					'content'    => ';',
					'line'       => 8,
					'column'     => 20,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			35 =>
				[
					'type'       => 'T_WHITESPACE',
					'code'       => 382,
					'content'    => '
',
					'line'       => 8,
					'column'     => 21,
					'length'     => 0,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			36 =>
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
			37 =>
				[
					'type'       => 'T_STRING',
					'code'       => 319,
					'content'    => 'get_posts',
					'line'       => 10,
					'column'     => 1,
					'length'     => 9,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			38 =>
				[
					'type'               => 'T_OPEN_PARENTHESIS',
					'code'               => 'PHPCS_T_OPEN_PARENTHESIS',
					'content'            => '(',
					'line'               => 10,
					'column'             => 10,
					'length'             => 1,
					'parenthesis_opener' => 38,
					'parenthesis_closer' => 60,
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
					'line'               => 10,
					'column'             => 11,
					'length'             => 1,
					'nested_parenthesis' =>
						[
							38 => 60,
						],
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			40 =>
				[
					'type'               => 'T_OPEN_SHORT_ARRAY',
					'code'               => 'PHPCS_T_OPEN_SHORT_ARRAY',
					'content'            => '[',
					'line'               => 10,
					'column'             => 12,
					'length'             => 1,
					'bracket_opener'     => 40,
					'bracket_closer'     => 58,
					'nested_parenthesis' =>
						[
							38 => 60,
						],
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			41 =>
				[
					'type'               => 'T_WHITESPACE',
					'code'               => 382,
					'content'            => '
',
					'line'               => 10,
					'column'             => 13,
					'length'             => 0,
					'nested_parenthesis' =>
						[
							38 => 60,
						],
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			42 =>
				[
					'type'               => 'T_WHITESPACE',
					'code'               => 382,
					'content'            => '    ',
					'line'               => 11,
					'column'             => 1,
					'orig_content'       => '	',
					'length'             => 4,
					'nested_parenthesis' =>
						[
							38 => 60,
						],
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			43 =>
				[
					'code'               => 323,
					'type'               => 'T_CONSTANT_ENCAPSED_STRING',
					'content'            => '\'post_type\'',
					'line'               => 11,
					'column'             => 5,
					'length'             => 11,
					'nested_parenthesis' =>
						[
							38 => 60,
						],
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			44 =>
				[
					'code'               => 382,
					'type'               => 'T_WHITESPACE',
					'content'            => '        ',
					'line'               => 11,
					'column'             => 16,
					'length'             => 8,
					'nested_parenthesis' =>
						[
							38 => 60,
						],
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			45 =>
				[
					'code'               => 268,
					'type'               => 'T_DOUBLE_ARROW',
					'content'            => '=>',
					'line'               => 11,
					'column'             => 24,
					'length'             => 2,
					'nested_parenthesis' =>
						[
							38 => 60,
						],
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			46 =>
				[
					'code'               => 382,
					'type'               => 'T_WHITESPACE',
					'content'            => ' ',
					'line'               => 11,
					'column'             => 26,
					'length'             => 1,
					'nested_parenthesis' =>
						[
							38 => 60,
						],
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			47 =>
				[
					'code'               => 323,
					'type'               => 'T_CONSTANT_ENCAPSED_STRING',
					'content'            => '\'page\'',
					'line'               => 11,
					'column'             => 27,
					'length'             => 6,
					'nested_parenthesis' =>
						[
							38 => 60,
						],
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			48 =>
				[
					'type'               => 'T_COMMA',
					'code'               => 'PHPCS_T_COMMA',
					'content'            => ',',
					'line'               => 11,
					'column'             => 33,
					'length'             => 1,
					'nested_parenthesis' =>
						[
							38 => 60,
						],
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			49 =>
				[
					'type'               => 'T_WHITESPACE',
					'code'               => 382,
					'content'            => '
',
					'line'               => 11,
					'column'             => 34,
					'length'             => 0,
					'nested_parenthesis' =>
						[
							38 => 60,
						],
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			50 =>
				[
					'type'               => 'T_WHITESPACE',
					'code'               => 382,
					'content'            => '    ',
					'line'               => 12,
					'column'             => 1,
					'orig_content'       => '	',
					'length'             => 4,
					'nested_parenthesis' =>
						[
							38 => 60,
						],
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			51 =>
				[
					'code'               => 323,
					'type'               => 'T_CONSTANT_ENCAPSED_STRING',
					'content'            => '\'suppress_filters\'',
					'line'               => 12,
					'column'             => 5,
					'length'             => 18,
					'nested_parenthesis' =>
						[
							38 => 60,
						],
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			52 =>
				[
					'code'               => 382,
					'type'               => 'T_WHITESPACE',
					'content'            => ' ',
					'line'               => 12,
					'column'             => 23,
					'length'             => 1,
					'nested_parenthesis' =>
						[
							38 => 60,
						],
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			53 =>
				[
					'code'               => 268,
					'type'               => 'T_DOUBLE_ARROW',
					'content'            => '=>',
					'line'               => 12,
					'column'             => 24,
					'length'             => 2,
					'nested_parenthesis' =>
						[
							38 => 60,
						],
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			54 =>
				[
					'code'               => 382,
					'type'               => 'T_WHITESPACE',
					'content'            => ' ',
					'line'               => 12,
					'column'             => 26,
					'length'             => 1,
					'nested_parenthesis' =>
						[
							38 => 60,
						],
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			55 =>
				[
					'type'               => 'T_FALSE',
					'code'               => 'PHPCS_T_FALSE',
					'content'            => 'false',
					'line'               => 12,
					'column'             => 27,
					'length'             => 5,
					'nested_parenthesis' =>
						[
							38 => 60,
						],
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			56 =>
				[
					'type'               => 'T_COMMA',
					'code'               => 'PHPCS_T_COMMA',
					'content'            => ',',
					'line'               => 12,
					'column'             => 32,
					'length'             => 1,
					'nested_parenthesis' =>
						[
							38 => 60,
						],
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			57 =>
				[
					'type'               => 'T_WHITESPACE',
					'code'               => 382,
					'content'            => '
',
					'line'               => 12,
					'column'             => 33,
					'length'             => 0,
					'nested_parenthesis' =>
						[
							38 => 60,
						],
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			58 =>
				[
					'type'               => 'T_CLOSE_SHORT_ARRAY',
					'code'               => 'PHPCS_T_CLOSE_SHORT_ARRAY',
					'content'            => ']',
					'line'               => 13,
					'column'             => 1,
					'length'             => 1,
					'bracket_opener'     => 40,
					'bracket_closer'     => 58,
					'nested_parenthesis' =>
						[
							38 => 60,
						],
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			59 =>
				[
					'code'               => 382,
					'type'               => 'T_WHITESPACE',
					'content'            => ' ',
					'line'               => 13,
					'column'             => 2,
					'length'             => 1,
					'nested_parenthesis' =>
						[
							38 => 60,
						],
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			60 =>
				[
					'type'               => 'T_CLOSE_PARENTHESIS',
					'code'               => 'PHPCS_T_CLOSE_PARENTHESIS',
					'content'            => ')',
					'line'               => 13,
					'column'             => 3,
					'length'             => 1,
					'parenthesis_opener' => 38,
					'parenthesis_closer' => 60,
					'level'              => 0,
					'conditions'         =>
						[
						],
				],
			61 =>
				[
					'type'       => 'T_SEMICOLON',
					'code'       => 'PHPCS_T_SEMICOLON',
					'content'    => ';',
					'line'       => 13,
					'column'     => 4,
					'length'     => 1,
					'level'      => 0,
					'conditions' =>
						[
						],
				],
			62 =>
				[
					'type'       => 'T_WHITESPACE',
					'code'       => 382,
					'content'    => '
',
					'line'       => 13,
					'column'     => 5,
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
