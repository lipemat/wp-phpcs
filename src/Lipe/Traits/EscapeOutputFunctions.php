<?php

namespace Lipe\Traits;

use PHP_CodeSniffer\Util\Tokens;

trait EscapeOutputFunctions
{

    public function __construct()
    {
        /**
         * Custom list of functions which escape values for output.
         *
         * @var string[]
         */
        $this->customEscapingFunctions = [];

        /**
         * Cache of previously added custom functions.
         *
         * Prevents having to do the same merges over and over again.
         *
         * @var array
         */
        $this->addedCustomFunctions = [];
    }

    protected function isScapeFunction($functionToken)
    {
        $this->mergeFunctionLists();

        $openParenthesis = $this->phpcsFile->findNext(Tokens::$emptyTokens, $functionToken + 1, null, true, null, true);

        if (isset($this->escapingFunctions[$this->tokens[$functionToken]['content']]) &&
            T_OPEN_PARENTHESIS === $this->tokens[$openParenthesis]['code'] &&
            isset($this->tokens[$openParenthesis]['parenthesis_opener']) &&
            isset($this->tokens[$openParenthesis]['parenthesis_closer'])
        ) {
            return true;
        }

        return false;
    }

    /**
     * Merge custom functions provided via a custom ruleset with the defaults, if we haven't already.
     *
     * @return void
     */
    protected function mergeFunctionLists()
    {
        $defaultScapeFunctions = ['sanitize' => true];

        if ($this->customEscapingFunctions !== $this->addedCustomFunctions) {
            $customEscapeFunctions = $this->merge_custom_array($this->customEscapingFunctions, [], false);

            $this->escapingFunctions = $this->merge_custom_array(
                $customEscapeFunctions,
                $defaultScapeFunctions
            );

            $this->addedCustomFunctions = $this->customEscapingFunctions;
        } else {
            $this->escapingFunctions = $defaultScapeFunctions;
        }

    }
}
