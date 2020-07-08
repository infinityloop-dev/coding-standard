<?php

declare(strict_types = 1);

namespace InfinityloopCodingStandard\Sniffs\Classes;

use \SlevomatCodingStandard\Helpers\TokenHelper;

/**
 * This sniff prohibits the use of protected variables and functions inside final class
 */
class FinalClassVisibilitySniff implements \PHP_CodeSniffer\Sniffs\Sniff
{
    /**
     * @var bool
     */
    public $ignoreInheritingClasses = true;

    public function register() : array
    {
        return [
            \T_VARIABLE,
            \T_FUNCTION,
        ];
    }

    public function process(\PHP_CodeSniffer\Files\File $phpcsFile, $variablePointer) : void
    {
        $tokens = $phpcsFile->getTokens();

        if (\count($tokens[$variablePointer]['conditions']) === 0) {
            return;
        }

        $classPointer = \array_keys($tokens[$variablePointer]['conditions'])[\count($tokens[$variablePointer]['conditions']) - 1];
        \assert(\is_int($classPointer));

        if ($tokens[$classPointer]['code'] !== \T_CLASS) {
            return;
        }

        $classVisibilityPointer = TokenHelper::findPreviousEffective($phpcsFile, $classPointer - 1);

        if ($tokens[$classVisibilityPointer]['code'] !== \T_FINAL) {
            return;
        }

        $extendsPointer = $phpcsFile->findNext(\T_EXTENDS, $classPointer);

        if ($this->ignoreInheritingClasses && $tokens[$extendsPointer]['code'] === \T_EXTENDS) {
            return;
        }

        $visibilityPointer = $this->findVisibilityPointer($phpcsFile, $variablePointer);

        if ($visibilityPointer === null || $tokens[$visibilityPointer]['code'] !== \T_PROTECTED) {
            return;
        }

        $fix = $phpcsFile->addFixableError(
            'Protected variables and functions inside final class are forbidden',
            $variablePointer,
            'FinalClassVisibility',
        );

        if (!$fix) {
            return;
        }

        $phpcsFile->fixer->beginChangeset();
        $phpcsFile->fixer->replaceToken($visibilityPointer, 'private');
        $phpcsFile->fixer->endChangeset();
    }

    /**
     * @return mixed
     */
    private function findVisibilityPointer(\PHP_CodeSniffer\Files\File $phpcsFile, $variablePointer)
    {
        $visibilityPointer = $phpcsFile->findPrevious([\T_PUBLIC, \T_PROTECTED, \T_PRIVATE], $variablePointer);

        if (\in_array($phpcsFile->getTokens()[$visibilityPointer]['code'], [\T_PUBLIC, \T_PROTECTED, \T_PRIVATE], true)) {
            return $visibilityPointer;
        }

        return null;
    }
}
