<?php

declare(strict_types = 1);

namespace InfinityloopCodingStandard\Sniffs\ControlStructures;

use \PHP_CodeSniffer\Files\File;
use \SlevomatCodingStandard\Helpers\TokenHelper;

class SwitchCommentSpacingSniff implements \PHP_CodeSniffer\Sniffs\Sniff
{
    public const CODE_SWITCH_COMMENT_INVALID_FORMAT = 'SwitchCommentInvalidSpacing';

    private const TAB_INDENT = "\t";
    private const SPACES_INDENT = '    ';

    public function register() : array
    {
        return [
            \T_SWITCH,
        ];
    }

    //@phpcs:ignore Squiz.Commenting.FunctionComment.ScalarTypeHintMissing
    public function process(File $phpcsFile, $stackPtr) : void
    {
        $tokens = $phpcsFile->getTokens();

        if (isset($tokens[$stackPtr]['scope_opener']) === false
            || isset($tokens[$stackPtr]['scope_closer']) === false
        ) {
            return;
        }

        $switch = $tokens[$stackPtr];
        $nextCase = $stackPtr;

        while (($nextCase = $this->findNextCase($phpcsFile, $nextCase + 1, $switch['scope_closer'])) !== false) {
            $opener = $tokens[$nextCase]['scope_opener'];
            $nextCloser = $tokens[$nextCase]['scope_closer'];

            $nextCode = $phpcsFile->findNext(\T_WHITESPACE, $opener + 1, $nextCloser, true);

            if ($tokens[$nextCode]['code'] === \T_CASE || $tokens[$nextCode]['code'] === \T_DEFAULT) {
                continue;
            }

            if ($tokens[$nextCode]['code'] === \T_COMMENT && $tokens[$nextCode]['line'] === $tokens[$nextCase]['line']) {
                $fix = $phpcsFile->addFixableError(
                    'Fallthrough comment has to be aligned to next line in case without body',
                    $nextCase,
                    self::CODE_SWITCH_COMMENT_INVALID_FORMAT,
                );

                if ($fix) {
                    $indentation = $this->getIndentation($phpcsFile, $this->getEndOfLineBefore($phpcsFile, $nextCase));

                    $phpcsFile->fixer->beginChangeset();
                    $phpcsFile->fixer->addContentBefore($nextCode, $phpcsFile->eolChar . $indentation);
                    $phpcsFile->fixer->endChangeset();
                }
            }

            if ($tokens[$nextCode]['code'] === \T_COMMENT) {
                continue;
            }

            $nextCode = $this->findNextCase($phpcsFile, $opener + 1, $nextCloser);

            $beforeEndOfCase = $phpcsFile->findPrevious(\T_WHITESPACE, ($nextCode === false ? $nextCloser : $nextCode) - 1, $nextCase, true);

            if ($tokens[$beforeEndOfCase]['code'] === \T_COMMENT &&
                ($nextCode !== false || $tokens[$nextCloser]['code'] === \T_CLOSE_CURLY_BRACKET)) {
                $beforeEndOfCase2 = $phpcsFile->findPrevious(\T_WHITESPACE, $beforeEndOfCase - 1, $nextCase, true);

                $linesBetween = $tokens[$beforeEndOfCase]['line'] - $tokens[$beforeEndOfCase2]['line'];

                if ($linesBetween === 0 || $linesBetween === 1) {
                    $fix = $phpcsFile->addFixableError(
                        'Expected one space between comment and last line of case\'s body',
                        $beforeEndOfCase,
                        self::CODE_SWITCH_COMMENT_INVALID_FORMAT,
                    );

                    if ($fix) {
                        $indentation = $this->getIndentation($phpcsFile, $this->getEndOfLineBefore($phpcsFile, $nextCase));

                        $phpcsFile->fixer->addContentBefore(
                            $beforeEndOfCase,
                            $linesBetween === 0 ? $phpcsFile->eolChar . $phpcsFile->eolChar . $indentation : $phpcsFile->eolChar . $indentation,
                        );

                        $phpcsFile->fixer->endChangeset();
                    }
                }
            }

            if ($nextCode !== false || !\in_array($tokens[$nextCloser]['code'], [\T_BREAK, \T_CONTINUE, \T_RETURN], true)) {
                continue;
            }

            $nextComment = $phpcsFile->findNext(\T_COMMENT, $nextCloser, null);

            $linesBetween = $tokens[$nextCloser]['line'] - $tokens[$nextComment]['line'];

            if ($linesBetween !== 0 && $linesBetween !== 1) {
                continue;
            }

            $fix = $phpcsFile->addFixableError(
                'Expected one space between comment and last line of case\'s body',
                $nextComment,
                self::CODE_SWITCH_COMMENT_INVALID_FORMAT,
            );

            if (!$fix) {
                continue;
            }

            $indentation = $this->getIndentation($phpcsFile, $this->getEndOfLineBefore($phpcsFile, $opener));

            $phpcsFile->fixer->beginChangeset();
            $phpcsFile->fixer->addContentBefore(
                $nextComment,
                $linesBetween === 0 ? $phpcsFile->eolChar . $phpcsFile->eolChar . $indentation : $phpcsFile->eolChar . $indentation,
            );
            $phpcsFile->fixer->endChangeset();
        }
    }

    private function findNextCase(File $phpcsFile, int|bool|null $stackPtr, ?int $end = null) : bool|int
    {
        $tokens = $phpcsFile->getTokens();

        while (($stackPtr = $phpcsFile->findNext([\T_CASE, \T_DEFAULT, \T_SWITCH], $stackPtr, $end)) !== false) {
            // Skip nested SWITCH statements; they are handled on their own.
            if ($tokens[$stackPtr]['code'] === \T_SWITCH) {
                $stackPtr = $tokens[$stackPtr]['scope_closer'];

                continue;
            }

            break;
        }

        return $stackPtr;
    }

    private function getEndOfLineBefore(File $phpcsFile, int $pointer) : int
    {
        $tokens = $phpcsFile->getTokens();

        $endOfLineBefore = null;

        $startPointer = $pointer - 1;

        while (true) {
            $possibleEndOfLinePointer = TokenHelper::findPrevious(
                $phpcsFile,
                \array_merge([\T_WHITESPACE, \T_OPEN_TAG, \T_OPEN_TAG_WITH_ECHO], TokenHelper::$inlineCommentTokenCodes),
                $startPointer,
            );

            if (
                $tokens[$possibleEndOfLinePointer]['code'] === \T_WHITESPACE
                && $tokens[$possibleEndOfLinePointer]['content'] === $phpcsFile->eolChar
            ) {
                $endOfLineBefore = $possibleEndOfLinePointer;

                break;
            }

            if ($tokens[$possibleEndOfLinePointer]['code'] === \T_OPEN_TAG || $tokens[$possibleEndOfLinePointer]['code'] === \T_OPEN_TAG_WITH_ECHO) {
                $endOfLineBefore = $possibleEndOfLinePointer;

                break;
            }

            if (
                \in_array($tokens[$possibleEndOfLinePointer]['code'], TokenHelper::$inlineCommentTokenCodes, true)
                && \substr($tokens[$possibleEndOfLinePointer]['content'], -1) === $phpcsFile->eolChar
            ) {
                $endOfLineBefore = $possibleEndOfLinePointer;

                break;
            }

            $startPointer = $possibleEndOfLinePointer - 1;
        }

        $endOfLineBefore = $endOfLineBefore;
        \assert(\is_int($endOfLineBefore));

        return $endOfLineBefore;
    }

    private function getIndentation(File $phpcsFile, int $endOfLinePointer) : string
    {
        $pointerAfterWhitespace = TokenHelper::findNextExcluding($phpcsFile, \T_WHITESPACE, $endOfLinePointer + 1);
        $actualIndentation = TokenHelper::getContent($phpcsFile, $endOfLinePointer + 1, $pointerAfterWhitespace - 1);

        if (\strlen($actualIndentation) !== 0) {
            return $actualIndentation . (\substr($actualIndentation, -1) === self::TAB_INDENT ? self::TAB_INDENT : self::SPACES_INDENT);
        }

        $tabPointer = TokenHelper::findPreviousContent($phpcsFile, \T_WHITESPACE, self::TAB_INDENT, $endOfLinePointer - 1);

        return $tabPointer !== null
            ? self::TAB_INDENT
            : self::SPACES_INDENT;
    }
}
