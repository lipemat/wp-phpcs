<?php

namespace Lipe\Sniffs\JS;

use Lipe\Traits\EscapeOutputFunctions;
use PHP_CodeSniffer\Util\Tokens;

class StringConcatSniff extends \WordPressVIPMinimum\Sniffs\JS\StringConcatSniff
{
    use EscapeOutputFunctions;

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param int $stackPtr The position of the current token in the stack passed in $tokens.
     *
     * @return void
     */
    public function process_token($stackPtr)
    {
        $nextToken = $this->phpcsFile->findNext(Tokens::$emptyTokens, $stackPtr + 1, null, true, null, true);

        if ($this->tokens[$nextToken]['code'] === T_CONSTANT_ENCAPSED_STRING && strpos($this->tokens[$nextToken]['content'], '<') !== false && preg_match('/\<\/[a-zA-Z]+/', $this->tokens[$nextToken]['content']) === 1) {
            if ($this->isScapeFunction($nextToken)) {
                // It's a custom scape function.
                return;
            }
        }

        $prevToken = $this->phpcsFile->findPrevious(Tokens::$emptyTokens, $stackPtr - 1, null, true, null, true);

        if ($this->tokens[$prevToken]['code'] === T_CONSTANT_ENCAPSED_STRING && strpos($this->tokens[$prevToken]['content'], '<') !== false && preg_match('/\<[a-zA-Z]+/', $this->tokens[$prevToken]['content']) === 1) {
            if ($this->isScapeFunction($prevToken)) {
                // It's a scape function.
                return;
            }
        }

        parent::process_token($stackPtr);
    }

}
