<?php

declare(strict_types = 1);

namespace InfinityloopCodingStandard\Sniffs\Namespaces;

use \SlevomatCodingStandard\Helpers\TokenHelper;
use \SlevomatCodingStandard\Helpers\UseStatementHelper;

class UseDoesNotStartWithBackslashSniff implements \PHP_CodeSniffer\Sniffs\Sniff
{
    public const CODE_DOES_NOT_START_WITH_BACKSLASH = 'UseDoesNotStartWithBackslash';

    public function register() : array
    {
        return [
            \T_USE,
        ];
    }

    //@phpcs:ignore Squiz.Commenting.FunctionComment.ScalarTypeHintMissing
    public function process(\PHP_CodeSniffer\Files\File $phpcsFile, $usePointer) : void
    {
        if (
            UseStatementHelper::isAnonymousFunctionUse($phpcsFile, $usePointer)
            || UseStatementHelper::isTraitUse($phpcsFile, $usePointer)
        ) {
            return;
        }

        $tokens = $phpcsFile->getTokens();
        $nextTokenPointer = TokenHelper::findNextEffective($phpcsFile, $usePointer + 1);
        \assert(\is_int($nextTokenPointer));

        if ($tokens[$nextTokenPointer]['code'] === \T_STRING
            && ($tokens[$nextTokenPointer]['content'] === 'function' || $tokens[$nextTokenPointer]['content'] === 'const')
        ) {
            $nextTokenPointer = TokenHelper::findNextEffective($phpcsFile, $nextTokenPointer + 1);
            \assert(\is_int($nextTokenPointer));
        }

        if ($tokens[$nextTokenPointer]['code'] === \T_NS_SEPARATOR) {
            return;
        }

        $fix = $phpcsFile->addFixableError(
            'Use statement must start with a backslash.',
            $nextTokenPointer,
            self::CODE_DOES_NOT_START_WITH_BACKSLASH,
        );

        if (!$fix) {
            return;
        }

        $phpcsFile->fixer->beginChangeset();
        $phpcsFile->fixer->addContentBefore($nextTokenPointer, '\\');
        $phpcsFile->fixer->endChangeset();
    }
}
