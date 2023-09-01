<?php
/**
 * Lipe.Performance.PostNotIn
 *
 * @package Lipe
 */

namespace Lipe\Sniffs\Performance;

use Lipe\Abstracts\AbstractArrayObjectAssignment;

/**
 * Check for uses of post__not_in and exclude in WP_Query and get_posts.
 *
 * @author Mat Lipe
 * @since  3.1.0
 *
 * @phpstan-type Group array{keys:array<int, string>, message:string, type:'error'|'warning'}
 */
class PostNotInSniff extends AbstractArrayObjectAssignment {
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
	 * Callback to process a confirmed key which doesn't need custom logic, but should always error.
	 *
	 * @param string       $key   Array index / key.
	 * @param mixed        $val   Assigned value.
	 * @param int          $line  Token line.
	 * @param array<mixed> $group Group definition.
	 *
	 * @return true
	 */
	public function callback( $key, $val, $line, $group ): bool {
		return true;
	}
}
