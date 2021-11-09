<?php

declare(strict_types = 1);

namespace InfinityloopCodingStandard\Sniffs\TypeHints;

use \PHP_CodeSniffer\Files\File;
use \SlevomatCodingStandard\Helpers\FunctionHelper;
use \SlevomatCodingStandard\Helpers\PropertyHelper;
use \SlevomatCodingStandard\Helpers\SniffSettingsHelper;
use \SlevomatCodingStandard\Helpers\TokenHelper;
use \SlevomatCodingStandard\Helpers\TypeHint;

/**
 * https://github.com/slevomat/coding-standard/blob/master/SlevomatCodingStandard/Sniffs/TypeHints/UnionTypeHintFormatSniff.php
 */
class UnionTypeHintFormatSniff implements \PHP_CodeSniffer\Sniffs\Sniff
{
    public const CODE_DISALLOWED_WHITESPACE = 'DisallowedWhitespace';
    public const CODE_REQUIRED_SHORT_NULLABLE = 'RequiredShortNullable';
    public const CODE_NULL_TYPE_HINT_NOT_ON_LAST_POSITION = 'NullTypeHintNotOnLastPosition';
    public const UNION_SEPARATOR_NOT_ON_LAST_POSITION = 'Union type separator (|) should be placed at end of the line';
    public const MULTILINE_UNION_WRONG_INDENTATION = 'Union types should be intended on same level as the one';

    /**
     * @return array<int, (int|string)>
     */
    public function register() : array
    {
        return \array_merge(
            [\T_VARIABLE],
            TokenHelper::$functionTokenCodes,
        );
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param int $pointer
     */
    //@phpcs:ignore Squiz.Commenting.FunctionComment.ScalarTypeHintMissing
    public function process(File $phpcsFile, $pointer) : void
    {
        if (!SniffSettingsHelper::isEnabledByPhpVersion(null, 80000)) {
            return;
        }

        $tokens = $phpcsFile->getTokens();

        if ($tokens[$pointer]['code'] === \T_VARIABLE) {
            if (!PropertyHelper::isProperty($phpcsFile, $pointer)) {
                return;
            }

            $propertyTypeHint = PropertyHelper::findTypeHint($phpcsFile, $pointer);

            if ($propertyTypeHint !== null) {
                $this->checkTypeHint($phpcsFile, $propertyTypeHint);
            }

            return;
        }

        $returnTypeHint = FunctionHelper::findReturnTypeHint($phpcsFile, $pointer);

        if ($returnTypeHint !== null) {
            $this->checkTypeHint($phpcsFile, $returnTypeHint);
        }

        foreach (FunctionHelper::getParametersTypeHints($phpcsFile, $pointer) as $parameterTypeHint) {
            if ($parameterTypeHint !== null) {
                $this->checkTypeHint($phpcsFile, $parameterTypeHint);
            }
        }
    }

    private function checkTypeHint(File $phpcsFile, TypeHint $typeHint) : void
    {
        $tokens = $phpcsFile->getTokens();

        $typeHintsCount = \substr_count($typeHint->getTypeHint(), '|') + 1;

        if ($typeHintsCount > 1) {
            $firstUnionType = $tokens[$typeHint->getStartPointer()];
            $isOneline = true;

            foreach (
                TokenHelper::findNextAll(
                    $phpcsFile,
                    [\T_TYPE_UNION],
                    $typeHint->getStartPointer(),
                    $typeHint->getEndPointer(),
                ) as $unionSeparator
            ) {
                if ($tokens[$unionSeparator]['line'] !== $firstUnionType['line'] && $tokens[$unionSeparator + 1]['content'] !== $phpcsFile->eolChar) {
                    $phpcsFile->addError(
                        self::UNION_SEPARATOR_NOT_ON_LAST_POSITION,
                        $unionSeparator,
                        self::UNION_SEPARATOR_NOT_ON_LAST_POSITION,
                    );
                }

                $nextUnionType = TokenHelper::findNextEffective($phpcsFile, $unionSeparator + 1);

                if ($tokens[$nextUnionType]['line'] === $firstUnionType['line']) {
                    continue;
                }

                $isOneline = false;

                if ($tokens[$typeHint->getStartPointer()]['column'] === $tokens[$nextUnionType]['column']) {
                    continue;
                }

                $fix = $phpcsFile->addFixableError(
                    self::MULTILINE_UNION_WRONG_INDENTATION,
                    $nextUnionType,
                    self::MULTILINE_UNION_WRONG_INDENTATION,
                );

                if (!$fix) {
                    continue;
                }

                $difference = $tokens[$typeHint->getStartPointer()]['column'] - $tokens[$nextUnionType]['column'];

                if ($difference === 0) {
                    continue;
                }

                $phpcsFile->fixer->beginChangeset();

                if ($difference > 0) {
                    $phpcsFile->fixer->addContentBefore($nextUnionType, \str_repeat(' ', \abs($difference)));

                    $phpcsFile->fixer->endChangeset();

                    continue;
                }

                for ($i = 0; $i < \abs($difference); $i++) {
                    $token = TokenHelper::findPrevious(
                        $phpcsFile,
                        [\T_WHITESPACE],
                        $nextUnionType - $i,
                    );

                    if (\strlen($tokens[$token]['content']) > 1) { //Handle multiple spaces
                        $phpcsFile->fixer->replaceToken(
                            $token,
                            \str_repeat(' ', \abs(\strlen($tokens[$token]['content']) - \abs($difference))),
                        );

                        break;
                    }

                    $phpcsFile->fixer->replaceToken($token, '');
                }

                $phpcsFile->fixer->endChangeset();
            }

            if ($isOneline) {
                $whitespacePointer = TokenHelper::findNext(
                    $phpcsFile,
                    \T_WHITESPACE,
                    $typeHint->getStartPointer() + 1,
                    $typeHint->getEndPointer(),
                );

                if ($whitespacePointer !== null) {
                    $originalTypeHint = TokenHelper::getContent(
                        $phpcsFile,
                        $typeHint->getStartPointer(),
                        $typeHint->getEndPointer(),
                    );

                    $fix = $phpcsFile->addFixableError(
                        \sprintf('Spaces in type hint "%s" are disallowed.', $originalTypeHint),
                        $typeHint->getStartPointer(),
                        self::CODE_DISALLOWED_WHITESPACE,
                    );

                    if ($fix) {
                        $this->fixTypeHint($phpcsFile, $typeHint, $typeHint->getTypeHint());
                    }
                }
            }
        }

        if (!$typeHint->isNullable()) {
            return;
        }

        $hasShortNullable = \strpos($typeHint->getTypeHint(), '?') === 0;

        if ($typeHintsCount === 2 && !$hasShortNullable) {
            $fix = $phpcsFile->addFixableError(
                \sprintf('Short nullable type hint in "%s" is required.', $typeHint->getTypeHint()),
                $typeHint->getStartPointer(),
                self::CODE_REQUIRED_SHORT_NULLABLE,
            );

            if ($fix) {
                $typeHintWithoutNull = self::getTypeHintContentWithoutNull($phpcsFile, $typeHint);
                $this->fixTypeHint($phpcsFile, $typeHint, '?' . $typeHintWithoutNull);
            }
        }

        if ($hasShortNullable || ($typeHintsCount === 2) || \strtolower($tokens[$typeHint->getEndPointer()]['content']) === 'null') {
            return;
        }

        $fix = $phpcsFile->addFixableError(
            \sprintf('Null type hint should be on last position in "%s".', $typeHint->getTypeHint()),
            $typeHint->getStartPointer(),
            self::CODE_NULL_TYPE_HINT_NOT_ON_LAST_POSITION,
        );

        if ($fix) {
            $this->fixTypeHint($phpcsFile, $typeHint, self::getTypeHintContentWithoutNull($phpcsFile, $typeHint) . '|null');
        }
    }

    private function getTypeHintContentWithoutNull(
        File $phpcsFile,
        \SlevomatCodingStandard\Helpers\TypeHint $typeHint,
    ) : string
    {
        $tokens = $phpcsFile->getTokens();

        if (\strtolower($tokens[$typeHint->getEndPointer()]['content']) === 'null') {
            $previousTypeHintPointer = TokenHelper::findPrevious(
                $phpcsFile,
                TokenHelper::getOnlyTypeHintTokenCodes(),
                $typeHint->getEndPointer() - 1,
            );

            return TokenHelper::getContent($phpcsFile, $typeHint->getStartPointer(), $previousTypeHintPointer);
        }

        $content = '';

        for ($i = $typeHint->getStartPointer(); $i <= $typeHint->getEndPointer(); $i++) {
            if (\strtolower($tokens[$i]['content']) === 'null') {
                $i = TokenHelper::findNext(
                    $phpcsFile,
                    TokenHelper::getOnlyTypeHintTokenCodes(),
                    $i + 1,
                );
            }

            $content .= $tokens[$i]['content'];
        }

        return $content;
    }

    private function fixTypeHint(
        File $phpcsFile,
        \SlevomatCodingStandard\Helpers\TypeHint $typeHint,
        string $fixedTypeHint,
    ) : void
    {
        $phpcsFile->fixer->beginChangeset();

        $phpcsFile->fixer->replaceToken($typeHint->getStartPointer(), $fixedTypeHint);

        for ($i = $typeHint->getStartPointer() + 1; $i <= $typeHint->getEndPointer(); $i++) {
            $phpcsFile->fixer->replaceToken($i, '');
        }

        $phpcsFile->fixer->endChangeset();
    }
}
