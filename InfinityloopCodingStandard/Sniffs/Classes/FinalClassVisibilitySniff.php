<?php

declare(strict_types=1);

namespace InfinityloopCodingStandard\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;
use RuntimeException;
use SlevomatCodingStandard\Helpers\ClassHelper;
use SlevomatCodingStandard\Helpers\DocCommentHelper;
use SlevomatCodingStandard\Helpers\FunctionHelper;
use SlevomatCodingStandard\Helpers\TokenHelper;
use const T_FINAL;
use const T_CLASS;
use const T_VARIABLE;

/**
 * This sniff prohibits the use of protected variables and functions inside final class
 */
class FinalClassVisibilitySniff implements Sniff
{
    public function register() : array
    {
        return [
            T_VARIABLE,
            T_FUNCTION,
        ];
    }

    public function process(File $phpcsFile, $variablePointer) : void
    {
        $tokens = $phpcsFile->getTokens();
        if (count($tokens[$variablePointer]['conditions']) === 0) {
            return;
        }
        /** @var int $classPointer */
        $classPointer = array_keys($tokens[$variablePointer]['conditions'])[count($tokens[$variablePointer]['conditions']) - 1];
        if ($tokens[$classPointer]['code'] !== T_CLASS) {
            return;
        }

        $classVisibilityPointer = TokenHelper::findPreviousEffective($phpcsFile, $classPointer - 1);
        if ($tokens[$classVisibilityPointer]['code'] !== T_FINAL) {
            return;
        }

        $visibilityPointer = $this->findVisibilityPointer($phpcsFile, $variablePointer);
        if ($visibilityPointer === null || $tokens[$visibilityPointer]['code'] !== T_PROTECTED) {
            return;
        }

        $fix = $phpcsFile->addFixableError(
            'Protected variables and function inside final class are forbidden',
            $variablePointer,
            'FinalClassVisibility'
        );

        if ($fix) {
            $phpcsFile->addWarning($visibilityPointer,$variablePointer,
                'FinalClassVisibility');
            $phpcsFile->fixer->beginChangeset();
            $phpcsFile->fixer->replaceToken($visibilityPointer, 'private');
            $phpcsFile->fixer->endChangeset();
        }
    }

    private function findVisibilityPointer(File $phpcsFile, $variablePointer)
    {
        $tokens = $phpcsFile->getTokens();

        for($i = 1; $i <= 3; $i++){
            $visibilityPointer = TokenHelper::findPreviousEffective($phpcsFile, $variablePointer - $i);
            if (in_array($tokens[$visibilityPointer]['code'], [T_PUBLIC, T_PROTECTED, T_PRIVATE], true)) {
                return $visibilityPointer;
            }
        }

        return null;
    }
}