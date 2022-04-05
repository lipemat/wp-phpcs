<?php
namespace Lipe\Sniffs\JS;

use Lipe\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**.
 *
 * Looks for instances of React's dangerouslySetInnerHMTL.
 */
class DangerouslySetInnerHTMLSniff extends Sniff {

    /**
     * A list of tokenizers this sniff supports.
     *
     * @var string[]
     */
    public $supportedTokenizers = [ 'JS' ];


    /**
     * Custom list of functions which escape values for output.
     **
     * @var string[]
     */
    public $customEscapingFunctions = [
        'sanitize',
    ];

    /**
     * Cache of previously added custom functions.
     *
     * Prevents having to do the same merges over and over again.
     *
     * @var array
     */
    protected $addedCustomFunctions = [];

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register() {
        return [
            T_STRING,
        ];
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param int $stackPtr The position of the current token in the stack passed in $tokens.
     *
     * @return void
     */
    public function process_token( $stackPtr ) {
        $this->mergeFunctionLists();

        if ( $this->tokens[ $stackPtr ]['content'] !== 'dangerouslySetInnerHTML' ) {
            // Looking for dangerouslySetInnerHTML only.
            return;
        }

        $nextToken = $this->phpcsFile->findNext( Tokens::$emptyTokens, $stackPtr + 1, null, true, null, true );

        if ( $this->tokens[ $nextToken ]['code'] !== T_EQUAL ) {
            // Not an assignment.
            return;
        }

        $nextNextToken = $this->phpcsFile->findNext( Tokens::$emptyTokens, $nextToken + 1, null, true, null, true );

        if ( $this->tokens[ $nextNextToken ]['code'] !== T_OBJECT ) {
            // Not react syntax.
            return;
        } else {
            $nextNextNextToken = $this->phpcsFile->findNext( Tokens::$emptyTokens, $nextNextToken + 2, null, true, null, true );

            if ( $this->tokens[ $nextNextNextToken ]['content'] === '__html' ) {
                $nextNextNextNextToken = $this->phpcsFile->findNext( Tokens::$emptyTokens, $nextNextNextToken + 2, null, true, null, true );

                if ( isset( $this->escapingFunctions[ $this->tokens[ $nextNextNextNextToken ]['content'] ] ) ) {
                    // It's a custom scape function.
                    return;
                }
            }
        }

        $message = "Any HTML passed to `%s` gets executed. Please make sure it's properly escaped.";
        $data    = [ $this->tokens[ $stackPtr ]['content'] ];
        $this->phpcsFile->addError( $message, $stackPtr, 'Found', $data );
    }

    /**
     * Merge custom functions provided via a custom ruleset with the defaults, if we haven't already.
     **
     * @return void
     */
    protected function mergeFunctionLists() {
        if ( $this->customEscapingFunctions !== $this->addedCustomFunctions ) {
            $customEscapeFunctions = $this->merge_custom_array( $this->customEscapingFunctions, [], false );

            $this->escapingFunctions = $this->merge_custom_array(
                $customEscapeFunctions,
                $this->escapingFunctions
            );

            $this->addedCustomFunctions = $this->customEscapingFunctions;
        }
    }
}
