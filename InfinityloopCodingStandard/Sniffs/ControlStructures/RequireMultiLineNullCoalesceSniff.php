<?php declare(strict_types = 1);

namespace InfinityloopCodingStandard\Sniffs\ControlStructures;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SlevomatCodingStandard\Helpers\TokenHelper;
use function array_merge;
use function in_array;
use function strlen;
use function substr;
use const T_COALESCE;
use const T_OPEN_TAG;
use const T_OPEN_TAG_WITH_ECHO;
use const T_WHITESPACE;

class RequireMultiLineNullCoalesceSniff implements Sniff
{

    public const CODE_MULTI_LINE_NULL_COALESCE_OPERATOR_NOT_USED = 'MultiLineNullCoalesceOperatorNotUsed';

    private const TAB_INDENT = "\t";
    private const SPACES_INDENT = '    ';

    /**
     * @return array<int, (int|string)>
     */
    public function register(): array
    {
        return [
            T_COALESCE,
        ];
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @param File $phpcsFile
     * @param int $coalescePointer
     */
    public function process(File $phpcsFile, $coalescePointer): void
    {
        $tokens = $phpcsFile->getTokens();

        /** @var int $variablePointer */
        $variablePointer = TokenHelper::findPrevious($phpcsFile, T_VARIABLE, $coalescePointer + 1);

        if ($tokens[$coalescePointer]['line'] !== $tokens[$variablePointer]['line']) {
            return;
        }

        $fix = $phpcsFile->addFixableError('Null coalesce operator should be reformatted to next line.', $coalescePointer, self::CODE_MULTI_LINE_NULL_COALESCE_OPERATOR_NOT_USED);

        if (!$fix) {
            return;
        }

        $endOfLineBeforeCoalescePointer = $this->getEndOfLineBefore($phpcsFile, $coalescePointer);

        $indentation = $this->getIndentation($phpcsFile, $endOfLineBeforeCoalescePointer);

        $phpcsFile->fixer->beginChangeset();
        $phpcsFile->fixer->addContentBefore($coalescePointer, $phpcsFile->eolChar . $indentation);
        $phpcsFile->fixer->endChangeset();
    }

    private function getEndOfLineBefore(File $phpcsFile, int $pointer): int
    {
        $tokens = $phpcsFile->getTokens();

        $endOfLineBefore = null;

        $startPointer = $pointer - 1;
        while (true) {
            $possibleEndOfLinePointer = TokenHelper::findPrevious(
                $phpcsFile,
                array_merge([T_WHITESPACE, T_OPEN_TAG, T_OPEN_TAG_WITH_ECHO], TokenHelper::$inlineCommentTokenCodes),
                $startPointer
            );
            if ($tokens[$possibleEndOfLinePointer]['code'] === T_WHITESPACE && $tokens[$possibleEndOfLinePointer]['content'] === $phpcsFile->eolChar) {
                $endOfLineBefore = $possibleEndOfLinePointer;
                break;
            }

            if ($tokens[$possibleEndOfLinePointer]['code'] === T_OPEN_TAG || $tokens[$possibleEndOfLinePointer]['code'] === T_OPEN_TAG_WITH_ECHO) {
                $endOfLineBefore = $possibleEndOfLinePointer;
                break;
            }

            if (
                in_array($tokens[$possibleEndOfLinePointer]['code'], TokenHelper::$inlineCommentTokenCodes, true)
                && substr($tokens[$possibleEndOfLinePointer]['content'], -1) === $phpcsFile->eolChar
            ) {
                $endOfLineBefore = $possibleEndOfLinePointer;
                break;
            }

            $startPointer = $possibleEndOfLinePointer - 1;
        }

        /** @var int $endOfLineBefore */
        $endOfLineBefore = $endOfLineBefore;
        return $endOfLineBefore;
    }

    private function getIndentation(File $phpcsFile, int $endOfLinePointer): string
    {
        $pointerAfterWhitespace = TokenHelper::findNextExcluding($phpcsFile, T_WHITESPACE, $endOfLinePointer + 1);
        $actualIndentation = TokenHelper::getContent($phpcsFile, $endOfLinePointer + 1, $pointerAfterWhitespace - 1);

        if (strlen($actualIndentation) !== 0) {
            return $actualIndentation . (substr($actualIndentation, -1) === self::TAB_INDENT ? self::TAB_INDENT : self::SPACES_INDENT);
        }

        $tabPointer = TokenHelper::findPreviousContent($phpcsFile, T_WHITESPACE, self::TAB_INDENT, $endOfLinePointer - 1);
        return $tabPointer !== null ? self::TAB_INDENT : self::SPACES_INDENT;
    }
}
