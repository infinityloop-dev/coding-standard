<?php

declare(strict_types = 1);

namespace InfinityloopCodingStandard\Sniffs\WhiteSpace;

class MemberVarSpacingSniff extends \PHP_CodeSniffer\Sniffs\AbstractVariableSniff
{
    public int $spacing = 1;
    public int $spacingBeforeFirst = 1;
    public bool $ignoreFirstMemberVar = false;

    /**
     * @return int|void
     */
    //@phpcs:ignore Squiz.Commenting.FunctionComment.ScalarTypeHintMissing
    protected function processMemberVar(\PHP_CodeSniffer\Files\File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $validPrefixes = \PHP_CodeSniffer\Util\Tokens::$methodPrefixes;
        $validPrefixes[] = \T_VAR;

        $startOfStatement = $phpcsFile->findPrevious($validPrefixes, $stackPtr - 1, null, false, null, true);

        if ($startOfStatement === false) {
            return;
        }

        $endOfStatement = $phpcsFile->findNext(\T_SEMICOLON, $stackPtr + 1, null, false, null, true);

        $ignore = $validPrefixes;
        $ignore[] = \T_WHITESPACE;

        $start = $startOfStatement;
        $prev = $phpcsFile->findPrevious($ignore, $startOfStatement - 1, null, true);

        if (isset(\PHP_CodeSniffer\Util\Tokens::$commentTokens[$tokens[$prev]['code']]) === true) {
            // Assume the comment belongs to the member var if it is on a line by itself.
            $prevContent = $phpcsFile->findPrevious(\PHP_CodeSniffer\Util\Tokens::$emptyTokens, $prev - 1, null, true);

            if ($tokens[$prevContent]['line'] !== $tokens[$prev]['line']) {
                // Check the spacing, but then skip it.
                $foundLines = $tokens[$startOfStatement]['line'] - $tokens[$prev]['line'] - 1;

                if ($foundLines > 0) {
                    $error = 'Expected 0 blank lines after member var comment; %s found';
                    $data = [$foundLines];
                    $fix = $phpcsFile->addFixableError($error, $prev, 'AfterComment', $data);

                    if ($fix === true) {
                        $phpcsFile->fixer->beginChangeset();

                        // Inline comments have the newline included in the content but
                        // docblock do not.
                        if ($tokens[$prev]['code'] === \T_COMMENT) {
                            $phpcsFile->fixer->replaceToken($prev, \rtrim($tokens[$prev]['content']));
                        }

                        for ($i = $prev + 1; $i <= $startOfStatement; $i++) {
                            if ($tokens[$i]['line'] === $tokens[$startOfStatement]['line']) {
                                break;
                            }

                            $phpcsFile->fixer->replaceToken($i, '');
                        }

                        $phpcsFile->fixer->addNewline($prev);
                        $phpcsFile->fixer->endChangeset();
                    }
                }//end if

                $start = $prev;
            }//end if
        }//end if

        // There needs to be n blank lines before the var, not counting comments.
        if ($start === $startOfStatement) {
            // No comment found.
            $first = $phpcsFile->findFirstOnLine(\PHP_CodeSniffer\Util\Tokens::$emptyTokens, $start, true);

            if ($first === false) {
                $first = $start;
            }
        } elseif ($tokens[$start]['code'] === \T_DOC_COMMENT_CLOSE_TAG) {
            $first = $tokens[$start]['comment_opener'];
        } else {
            $first = $phpcsFile->findPrevious(\PHP_CodeSniffer\Util\Tokens::$emptyTokens, $start - 1, null, true);
            $first = $phpcsFile->findNext(\PHP_CodeSniffer\Util\Tokens::$commentTokens, $first + 1);
        }

        // Determine if this is the first member var.
        $prev = $phpcsFile->findPrevious(\T_WHITESPACE, $first - 1, null, true);

        if ($tokens[$prev]['code'] === \T_CLOSE_CURLY_BRACKET
            && isset($tokens[$prev]['scope_condition']) === true
            && $tokens[$tokens[$prev]['scope_condition']]['code'] === \T_FUNCTION
        ) {
            return;
        }

        $prevVar = $phpcsFile->findPrevious(\T_VARIABLE, $first - 1);

        if ($this->ignoreFirstMemberVar && $tokens[$prevVar]['code'] !== \T_VARIABLE) {
            return;
        }

        if ($tokens[$prev]['code'] === \T_OPEN_CURLY_BRACKET
            && isset(\PHP_CodeSniffer\Util\Tokens::$ooScopeTokens[$tokens[$tokens[$prev]['scope_condition']]['code']]) === true
        ) {
            $errorMsg = 'Expected %s blank line(s) before first member var; %s found';
            $errorCode = 'FirstIncorrect';
            $spacing = (int) $this->spacingBeforeFirst;
        } else {
            $errorMsg = 'Expected %s blank line(s) before member var; %s found';
            $errorCode = 'Incorrect';
            $spacing = (int) $this->spacing;
        }

        $foundLines = $tokens[$first]['line'] - $tokens[$prev]['line'] - 1;

        if ($errorCode === 'FirstIncorrect') {
            $phpcsFile->recordMetric($stackPtr, 'Member var spacing before first', $foundLines);
        } else {
            $phpcsFile->recordMetric($stackPtr, 'Member var spacing before', $foundLines);
        }

        if ($foundLines === $spacing) {
            if ($endOfStatement !== false) {
                return $endOfStatement;
            }

            return;
        }

        $data = [
            $spacing,
            $foundLines,
        ];

        $fix = $phpcsFile->addFixableError($errorMsg, $startOfStatement, $errorCode, $data);

        if ($fix === true) {
            $phpcsFile->fixer->beginChangeset();

            for ($i = $prev + 1; $i < $first; $i++) {
                if ($tokens[$i]['line'] === $tokens[$prev]['line']) {
                    continue;
                }

                if ($tokens[$i]['line'] === $tokens[$first]['line']) {
                    for ($x = 1; $x <= $spacing; $x++) {
                        $phpcsFile->fixer->addNewlineBefore($i);
                    }

                    break;
                }

                $phpcsFile->fixer->replaceToken($i, '');
            }

            $phpcsFile->fixer->endChangeset();
        }//end if

        if ($endOfStatement !== false) {
            return $endOfStatement;
        }
    }

    //@phpcs:ignore Squiz.Commenting.FunctionComment.ScalarTypeHintMissing
    protected function processVariable(\PHP_CodeSniffer\Files\File $phpcsFile, $stackPtr) : void
    {
    }

    //@phpcs:ignore Squiz.Commenting.FunctionComment.ScalarTypeHintMissing
    protected function processVariableInString(\PHP_CodeSniffer\Files\File $phpcsFile, $stackPtr) : void
    {
    }
}
