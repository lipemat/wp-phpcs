<?php

namespace Lipe\Sniffs\JS;

use Lipe\Traits\EscapeOutputFunctions;
use PHP_CodeSniffer\Util\Tokens;

class StrippingTagsSniff extends \WordPressVIPMinimum\Sniffs\JS\StrippingTagsSniff
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
        if ($this->tokens[$stackPtr]['content'] !== 'html') {
            // Looking for html() only.
            return;
        }

        $functionToken = $this->phpcsFile->findNext(Tokens::$functionNameTokens, $stackPtr + 1);

        if ($this->isScapeFunction($functionToken)) {
            // It's a scape function.
            return;
        }

        parent::process_token($stackPtr);
    }
}
