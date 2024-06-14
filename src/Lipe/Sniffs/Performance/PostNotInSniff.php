<?php
/**
 * Lipe.Performance.PostNotIn
 *
 * @package Lipe
 */

namespace Lipe\Sniffs\Performance;

use Lipe\Abstracts\AbstractArrayObjectAssignment;
use PHPCSUtils\Utils\TextStrings;
use WordPressCS\WordPress\Helpers\ContextHelper;
use WordPressVIPMinimum\Sniffs\Performance\WPQueryParamsSniff;

/**
 * Check for uses of post__not_in and exclude in WP_Query and get_posts.
 *
 * @author Mat Lipe
 * @since  3.1.0
 *
 * @see    WPQueryParamsSniff
 *
 * @phpstan-type Group array{keys: array<int, string>, message: string, type: 'error'|'warning'}
 */
class PostNotInSniff extends AbstractArrayObjectAssignment {
	/**
	 * Whether the current $stackPtr being scanned is nested in a function call to get_users().
	 *
	 * @var bool
	 */
	private $in_non_post_calls = false;


	/**
	 * Groups of variables to restrict.
	 *
	 * @return array<string, Group>
	 */
	public function getGroups(): array {
		return [
			'found'   => [
				'type'    => 'warning',
				'message' => 'Using `post__not_in` should be done with caution, see https://docs.wpvip.com/how-tos/improve-performance-by-removing-usage-of-post__not_in/ for more information.',
				'keys'    => [
					'post__not_in',
				],
			],
			'exclude' => [
				'type'    => 'warning',
				'message' => 'Using `exclude`, subsequently used by `post__not_in`, should be done with caution, see https://docs.wpvip.com/how-tos/improve-performance-by-removing-usage-of-post__not_in/ for more information.',
				'keys'    => [
					'exclude',
				],
			],
		];
	}


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param int $stackPtr The position of the current token in the stack.
	 *
	 * @return void
	 */
	public function process_token( $stackPtr ): void {
		$content = $this->tokens[ $stackPtr ]['content'];
		if ( T_OBJECT_OPERATOR === $this->tokens[ $stackPtr ]['code'] ) {
			$prop = $this->phpcsFile->findNext( \T_OPEN_CURLY_BRACKET, ( $stackPtr + 1 ), null, true, null, true );
			if ( false !== $prop ) {
				$content = $this->tokens[ $prop ]['content'];
			}
		}

		if ( 'exclude' === TextStrings::stripQuotes( $content ) ) {
			$this->in_non_post_calls = (bool) ContextHelper::is_in_function_call( $this->phpcsFile, $stackPtr, [ 'get_users' => true ], true, true );

			if ( false === $this->in_non_post_calls ) {
				$this->in_non_post_calls = (bool) ContextHelper::is_in_function_call( $this->phpcsFile, $stackPtr, [ 'get_terms' => true ], true, true );
			}

			if ( false === $this->in_non_post_calls ) {
				$this->in_non_post_calls = $this->is_in_class( 'Get_Terms', $stackPtr );
			}
		}

		parent::process_token( $stackPtr );
	}


	/**
	 * Callback to process a confirmed key which doesn't need custom logic, but should always error.
	 *
	 * @param string       $key   Array index / key.
	 * @param mixed        $val   Assigned value.
	 * @param int          $line  Token line.
	 * @param array<mixed> $group Group definition.
	 *
	 * @return bool
	 */
	public function callback( $key, $val, $line, $group ) {
		return ! ( 'exclude' === $key && false !== $this->in_non_post_calls );
	}
}
