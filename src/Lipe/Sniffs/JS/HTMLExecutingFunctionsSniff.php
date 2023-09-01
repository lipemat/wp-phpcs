<?php
/**
 * Lipe.JS.HTMLExecutingFunctions
 *
 * @package wp-phpcs\Lipe
 */

namespace Lipe\Sniffs\JS;

use Lipe\Abstracts\AbstractEscapeOutputFunctions;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Flags functions, which are executing HTML passed to it.
 */
class HTMLExecutingFunctionsSniff extends AbstractEscapeOutputFunctions {
	/**
	 * List of HTML executing functions.
	 *
	 * Name of function => content or target.
	 * Value indicates whether the function's arg is the content to be inserted, or the target where the inserted
	 * content is to be inserted before/after/replaced. For the latter, the content is in the preceding method's arg.
	 *
	 * @var array<string, string>
	 */
	public $HTMLExecutingFunctions = [
		'after'        => 'content', // jQuery.
		'append'       => 'content', // jQuery.
		'appendTo'     => 'target',  // jQuery.
		'before'       => 'content', // jQuery.
		'html'         => 'content', // jQuery.
		'insertAfter'  => 'target',  // jQuery.
		'insertBefore' => 'target',  // jQuery.
		'prepend'      => 'content', // jQuery.
		'prependTo'    => 'target',  // jQuery.
		'replaceAll'   => 'target',  // jQuery.
		'replaceWith'  => 'content', // jQuery.
		'write'        => 'content',
		'writeln'      => 'content',
	];


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param integer $stackPtr The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process_token( $stackPtr ): void {
		if ( ! isset( $this->HTMLExecutingFunctions[ $this->tokens[ $stackPtr ]['content'] ] ) ) {
			// Looking for specific functions only.
			return;
		}

		$functionToken = $this->phpcsFile->findNext( Tokens::$functionNameTokens, ( $stackPtr + 1 ) );
		if ( $this->isEscapeFunction( $functionToken ) ) {
			return;
		}

		if ( 'content' === $this->HTMLExecutingFunctions[ $this->tokens[ $stackPtr ]['content'] ] ) {
			$nextToken = $this->phpcsFile->findNext( Tokens::$emptyTokens, $stackPtr + 1, null, true, null, true );

			if ( T_OPEN_PARENTHESIS !== $this->tokens[ $nextToken ]['code'] ) {
				// Not a function.
				return;
			}

			$parenthesis_closer = $this->tokens[ $nextToken ]['parenthesis_closer'];

			while ( false !== $nextToken && $nextToken < $parenthesis_closer ) {
				$nextToken = $this->phpcsFile->findNext( Tokens::$emptyTokens, $nextToken + 1, null, true, null, true );
				if ( T_STRING === $this->tokens[ $nextToken ]['code'] ) { // Contains a variable, function call or something else dynamic.
					$message = 'Any HTML passed to `%s` gets executed. Make sure it\'s properly escaped.';
					$data = [ $this->tokens[ $stackPtr ]['content'] ];
					$this->phpcsFile->addWarning( $message, $stackPtr, $this->tokens[ $stackPtr ]['content'], $data );

					return;
				}
			}
		} elseif ( 'target' === $this->HTMLExecutingFunctions[ $this->tokens[ $stackPtr ]['content'] ] ) {
			$prevToken = $this->phpcsFile->findPrevious( Tokens::$emptyTokens, $stackPtr - 1, null, true, null, true );

			if ( false === $prevToken || T_OBJECT_OPERATOR !== $this->tokens[ $prevToken ]['code'] ) {
				return;
			}

			$prevPrevToken = $this->phpcsFile->findPrevious( Tokens::$emptyTokens, $prevToken - 1, null, true, null, true );

			if ( T_CLOSE_PARENTHESIS !== $this->tokens[ $prevPrevToken ]['code'] ) {
				// Not a function call, but may be a variable containing an element reference, so just
				// flag all remaining instances of these target HTML executing functions.
				$message = 'Any HTML used with `%s` gets executed. Make sure it\'s properly escaped.';
				$data = [ $this->tokens[ $stackPtr ]['content'] ];
				$this->phpcsFile->addWarning( $message, $stackPtr, $this->tokens[ $stackPtr ]['content'], $data );

				return;
			}

			// Check if it's a function call (typically $() ) that contains a dynamic part.
			$parenthesis_opener = $this->tokens[ $prevPrevToken ]['parenthesis_opener'];

			while ( false !== $prevPrevToken && $prevPrevToken > $parenthesis_opener ) {
				$prevPrevToken = $this->phpcsFile->findPrevious( Tokens::$emptyTokens, $prevPrevToken - 1, null, true, null, true );
				if ( T_STRING === $this->tokens[ $prevPrevToken ]['code'] ) { // Contains a variable, function call or something else dynamic.
					$message = 'Any HTML used with `%s` gets executed. Make sure it\'s properly escaped.';
					$data = [ $this->tokens[ $stackPtr ]['content'] ];
					$this->phpcsFile->addWarning( $message, $stackPtr, $this->tokens[ $stackPtr ]['content'], $data );

					return;
				}
			}
		}
	}

}
