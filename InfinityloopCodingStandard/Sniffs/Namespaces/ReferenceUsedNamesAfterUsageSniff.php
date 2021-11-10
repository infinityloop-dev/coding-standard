<?php

declare(strict_types = 1);

namespace InfinityloopCodingStandard\Sniffs\Namespaces;

use \PHPStan\PhpDocParser\Ast\ConstExpr\ConstFetchNode;
use \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use \PHP_CodeSniffer\Files\File;
use \SlevomatCodingStandard\Helpers\AnnotationHelper;
use \SlevomatCodingStandard\Helpers\ClassHelper;
use \SlevomatCodingStandard\Helpers\CommentHelper;
use \SlevomatCodingStandard\Helpers\NamespaceHelper;
use \SlevomatCodingStandard\Helpers\ReferencedNameHelper;
use \SlevomatCodingStandard\Helpers\StringHelper;
use \SlevomatCodingStandard\Helpers\TokenHelper;
use \SlevomatCodingStandard\Helpers\UseStatement;
use \SlevomatCodingStandard\Helpers\UseStatementHelper;

/**
 * https://github.com/slevomat/coding-standard/blob/master/SlevomatCodingStandard/Sniffs/Namespaces/ReferenceUsedNamesOnlySniff.php
 */
class ReferenceUsedNamesAfterUsageSniff implements \PHP_CodeSniffer\Sniffs\Sniff
{
    public const CODE_REFERENCE_VIA_FULLY_QUALIFIED_NAME = 'ReferenceUsedNamesAfterUsage';
    public const CODE_REFERENCE_VIA_FULLY_QUALIFIED_NAME_WITHOUT_NAMESPACE = 'ReferenceViaFullyQualifiedNameWithoutNamespace';
    public const CODE_REFERENCE_VIA_FALLBACK_GLOBAL_NAME = 'ReferenceViaFallbackGlobalName';

    private const SOURCE_CODE = 1;
    private const SOURCE_ANNOTATION = 2;
    private const SOURCE_ANNOTATION_CONSTANT_FETCH = 3;
    private const SOURCE_ATTRIBUTE = 4;

    public ?int $count = null;
    public ?int $length = null;
    public ?int $lineLength = null;
    public ?int $lineClassLength = null;

    /**
     * @return array<int, (int|string)>
     */
    public function register() : array
    {
        return [
            \T_OPEN_TAG,
        ];
    }

    //@phpcs:ignore Squiz.Commenting.FunctionComment.ScalarTypeHintMissing
    public function process(File $phpcsFile, $openTagPointer) : void
    {
        if (TokenHelper::findPrevious($phpcsFile, \T_OPEN_TAG, $openTagPointer - 1) !== null) {
            return;
        }

        $tokens = $phpcsFile->getTokens();
        $references = $this->getReferences($phpcsFile, $openTagPointer);
        $definedClassesIndex = [];

        foreach (ClassHelper::getAllNames($phpcsFile) as $definedClassPointer => $definedClassName) {
            $definedClassesIndex[\strtolower($definedClassName)] = NamespaceHelper::resolveClassName(
                $phpcsFile,
                $definedClassName,
                $definedClassPointer,
            );
        }

        $classReferencesIndex = [];
        $classReferences = \array_filter($references, static function (\stdClass $reference) : bool {
            return $reference->source === self::SOURCE_CODE && $reference->isClass;
        });

        foreach ($classReferences as $classReference) {
            $classReferencesIndex[\strtolower($classReference->name)] = NamespaceHelper::resolveName(
                $phpcsFile,
                $classReference->name,
                $classReference->type,
                $classReference->startPointer,
            );
        }

        $namespacePointers = NamespaceHelper::getAllNamespacesPointers($phpcsFile);
        $referenceErrors = [];
        $referenced = [];

        foreach ($references as $reference) {
            $canonicalName = NamespaceHelper::normalizeToCanonicalName($reference->name);

            if (isset($referenced[$canonicalName])) {
                $referenced[$canonicalName]++;
            } else {
                $referenced[$canonicalName] = 1;
            }
        }

        foreach ($references as $reference) {
            $useStatements = UseStatementHelper::getUseStatementsForPointer($phpcsFile, $reference->startPointer);
            $name = $reference->name;
            $startPointer = $reference->startPointer;
            $canonicalName = NamespaceHelper::normalizeToCanonicalName($name);
            $isFullyQualified = NamespaceHelper::isFullyQualifiedName($name);

            if ($reference->isClass === true && !NamespaceHelper::hasNamespace($name) && $isFullyQualified) {
                continue;
            }

            if ($reference->isFunction === true && !NamespaceHelper::hasNamespace($name) && $isFullyQualified) {
                continue;
            }

            if ($reference->isConstant === true && !NamespaceHelper::hasNamespace($name) && $isFullyQualified) {
                continue;
            }

            if (
                $isFullyQualified
                && !NamespaceHelper::hasNamespace($name)
                && $namespacePointers === []
            ) {
                $label = \sprintf('Class %s', $name);

                $fix = $phpcsFile->addFixableError(\sprintf(
                    '%s should not be referenced via a fully qualified name, but via an unqualified name without the leading \\, because '
                    . 'the file does not have a namespace and the type cannot be put in a use statement.',
                    $label,
                ), $startPointer, self::CODE_REFERENCE_VIA_FULLY_QUALIFIED_NAME_WITHOUT_NAMESPACE);

                if ($fix) {
                    $phpcsFile->fixer->beginChangeset();

                    if ($reference->source === self::SOURCE_ANNOTATION) {
                        $fixedAnnotationContent = AnnotationHelper::fixAnnotationType(
                            $phpcsFile,
                            $reference->annotation,
                            $reference->nameNode,
                            new IdentifierTypeNode(\substr($reference->name, 1)),
                        );

                        $phpcsFile->fixer->replaceToken($startPointer, $fixedAnnotationContent);

                        for ($i = $startPointer + 1; $i <= $reference->endPointer; $i++) {
                            $phpcsFile->fixer->replaceToken($i, '');
                        }
                    } elseif ($reference->source === self::SOURCE_ANNOTATION_CONSTANT_FETCH) {
                        $fixedAnnotationContent = AnnotationHelper::fixAnnotationConstantFetchNode(
                            $phpcsFile,
                            $reference->annotation,
                            $reference->constantFetchNode,
                            new ConstFetchNode(\substr($reference->name, 1), $reference->constantFetchNode->name),
                        );

                        $phpcsFile->fixer->replaceToken($startPointer, $fixedAnnotationContent);

                        for ($i = $startPointer + 1; $i <= $reference->endPointer; $i++) {
                            $phpcsFile->fixer->replaceToken($i, '');
                        }
                    } else {
                        $phpcsFile->fixer->replaceToken($startPointer, \substr($tokens[$startPointer]['content'], 1));
                    }

                    $phpcsFile->fixer->endChangeset();
                }
            } else {
                $shouldBeUsed = NamespaceHelper::hasNamespace($name);

                if (!$shouldBeUsed) {
                    $shouldBeUsed = $isFullyQualified;
                }

                $tokens = $phpcsFile->getTokens();

                $start = TokenHelper::findFirstTokenOnLine($phpcsFile, $reference->startPointer);
                $end = TokenHelper::findLastTokenOnLine($phpcsFile, $reference->startPointer);
                $lineLength = \strlen(TokenHelper::getContent($phpcsFile, $start, $end));

                if (!$shouldBeUsed
                    || ($this->count === null || $this->count > $referenced[$canonicalName])
                    && ($this->length === null || $this->length > \strlen($canonicalName))
                    && ($this->lineLength === null || $this->lineClassLength === null || $this->lineClassLength > \strlen($canonicalName)
                        || $this->lineLength > $lineLength)
                ) {
                    continue;
                }

                $reason = '';

                if ($this->count !== null && $referenced[$canonicalName] >= $this->count) {
                    $reason = 'because it\'s used more than ' . $this->count . ' times.';
                }

                if ($this->length !== null && $this->length <= \strlen($canonicalName)) {
                    $reason = $reason === ''
                        ? 'because it\'s length is more than ' . $this->length . ' symbols.'
                        : 'because it\'s used more than ' . $this->count . ' times and it\'s length is more than '
                        . $this->length . ' symbols.';
                }

                if ($this->lineLength !== null && $this->lineClassLength !== null && \strlen($canonicalName) >= $this->lineClassLength
                    && $lineLength >= $this->lineLength) {
                    $reason = 'because line length is more than ' . $this->lineLength
                        . ' symbols and class length is more than ' . $this->lineClassLength . ' symbols.';
                }

                $referenceErrors[] = (object) [
                    'reference' => $reference,
                    'canonicalName' => $canonicalName,
                    'reason' => $reason,
                ];
            }
        }

        if (\count($referenceErrors) === 0) {
            return;
        }

        $alreadyAddedUses = [
            UseStatement::TYPE_CLASS => [],
            UseStatement::TYPE_FUNCTION => [],
            UseStatement::TYPE_CONSTANT => [],
        ];

        $phpcsFile->fixer->beginChangeset();

        foreach ($referenceErrors as $referenceData) {
            $reference = $referenceData->reference;
            $startPointer = $reference->startPointer;
            $canonicalName = $referenceData->canonicalName;
            $useStatements = UseStatementHelper::getUseStatementsForPointer($phpcsFile, $reference->startPointer);
            [$nameToReference, $isConflicting] = $this->getNormalizedClassName($reference->name, $useStatements, $phpcsFile);
            $canonicalNameToReference = \strtolower($nameToReference);

            $canBeFixed = \array_reduce(
                $alreadyAddedUses[$reference->type],
                static function (bool $carry, string $use) use ($canonicalName) : bool {
                    $useLastName = \strtolower(NamespaceHelper::getLastNamePart($use));
                    $canonicalLastName = \strtolower(NamespaceHelper::getLastNamePart($canonicalName));

                    return $useLastName === $canonicalLastName
                        ? false
                        : $carry;
                },
                true,
            );

            if (
                (
                    $reference->isClass
                    && \array_key_exists($canonicalNameToReference, $definedClassesIndex)
                    && $canonicalName !== NamespaceHelper::normalizeToCanonicalName($definedClassesIndex[$canonicalNameToReference])
                )
                || (
                    $reference->isClass
                    && \array_key_exists($canonicalNameToReference, $classReferencesIndex)
                    && $canonicalName !== NamespaceHelper::normalizeToCanonicalName($classReferencesIndex[$canonicalNameToReference])
                )
            ) {
                $canBeFixed = false;
            }

            foreach ($useStatements as $useStatement) {
                if ($useStatement->getType() !== $reference->type) {
                    continue;
                }

                if ($useStatement->getFullyQualifiedTypeName() === $canonicalName) {
                    continue;
                }

                if ($useStatement->getCanonicalNameAsReferencedInFile() !== $canonicalNameToReference) {
                    continue;
                }

                $canBeFixed = false;

                break;
            }

            $label = \sprintf('Class %s', $reference->name);
            $errorCode = self::CODE_REFERENCE_VIA_FULLY_QUALIFIED_NAME;
            $errorMessage = \sprintf(
                '%s should not be referenced via a fully qualified name, but via a use statement ' . $referenceData->reason,
                $label,
            );

            if (!$canBeFixed) {
                $phpcsFile->addError($errorMessage, $startPointer, $errorCode);

                continue;
            }

            $fix = $phpcsFile->addFixableError($errorMessage, $startPointer, $errorCode);

            if (!$fix) {
                continue;
            }

            $addUse = !\in_array($canonicalName, $alreadyAddedUses[$reference->type], true);

            if ($reference->isClass
                && \array_key_exists($canonicalNameToReference, $definedClassesIndex)
            ) {
                $addUse = false;
            }

            foreach ($useStatements as $useStatement) {
                if ($useStatement->getType() !== $reference->type
                    || $useStatement->getFullyQualifiedTypeName() !== $canonicalName
                ) {
                    continue;
                }

                $nameToReference = $useStatement->getNameAsReferencedInFile();
                $addUse = false;
            }

            if ($addUse) {
                $useStatementPlacePointer = $this->getUseStatementPlacePointer($phpcsFile, $openTagPointer, $useStatements);
                $useTypeName = UseStatement::getTypeName($reference->type);
                $useTypeFormatted = $useTypeName !== null
                    ? \sprintf('%s ', $useTypeName)
                    : '';

                $phpcsFile->fixer->addNewline($useStatementPlacePointer);

                if ($isConflicting === true) {
                    $phpcsFile->fixer->addContent(
                        $useStatementPlacePointer,
                        \sprintf('use %s%s as %s;', $useTypeFormatted, $canonicalName, $nameToReference),
                    );
                } else {
                    $phpcsFile->fixer->addContent($useStatementPlacePointer, \sprintf('use %s%s;', $useTypeFormatted, $canonicalName));
                }

                $alreadyAddedUses[$reference->type][] = $canonicalName;
            }

            if ($reference->source === self::SOURCE_ANNOTATION) {
                $fixedAnnotationContent = AnnotationHelper::fixAnnotationType(
                    $phpcsFile,
                    $reference->annotation,
                    $reference->nameNode,
                    new IdentifierTypeNode($nameToReference),
                );
                $phpcsFile->fixer->replaceToken($startPointer, $fixedAnnotationContent);
            } elseif ($reference->source === self::SOURCE_ANNOTATION_CONSTANT_FETCH) {
                $fixedAnnotationContent = AnnotationHelper::fixAnnotationConstantFetchNode(
                    $phpcsFile,
                    $reference->annotation,
                    $reference->constantFetchNode,
                    new ConstFetchNode($nameToReference, $reference->constantFetchNode->name),
                );
                $phpcsFile->fixer->replaceToken($startPointer, $fixedAnnotationContent);
            } elseif ($reference->source === self::SOURCE_ATTRIBUTE) {
                $attributeContent = TokenHelper::getContent($phpcsFile, $startPointer, $reference->endPointer);
                $fixedAttributeContent = \preg_replace(
                    '~(?<=\W)' . \preg_quote($reference->name, '~') . '(?=\W)~',
                    $nameToReference,
                    $attributeContent,
                );
                $phpcsFile->fixer->replaceToken($startPointer, $fixedAttributeContent);
            } else {
                $phpcsFile->fixer->replaceToken($startPointer, $nameToReference);
            }

            for ($i = $startPointer + 1; $i <= $reference->endPointer; $i++) {
                $phpcsFile->fixer->replaceToken($i, '');
            }
        }

        $phpcsFile->fixer->endChangeset();
    }

    private function getUseStatementPlacePointer(\PHP_CodeSniffer\Files\File $phpcsFile, int $openTagPointer, array $useStatements) : int
    {
        if (\count($useStatements) !== 0) {
            $lastUseStatement = \array_values($useStatements)[\count($useStatements) - 1];

            return TokenHelper::findNext($phpcsFile, \T_SEMICOLON, $lastUseStatement->getPointer() + 1);
        }

        $namespacePointer = TokenHelper::findNext($phpcsFile, \T_NAMESPACE, $openTagPointer + 1);

        if ($namespacePointer !== null) {
            return TokenHelper::findNext($phpcsFile, [\T_SEMICOLON, \T_OPEN_CURLY_BRACKET], $namespacePointer + 1);
        }

        $tokens = $phpcsFile->getTokens();

        $useStatementPlacePointer = $openTagPointer;

        $nonWhitespacePointerAfterOpenTag = TokenHelper::findNextExcluding($phpcsFile, \T_WHITESPACE, $openTagPointer + 1);

        if (\in_array($tokens[$nonWhitespacePointerAfterOpenTag]['code'], \PHP_CodeSniffer\Util\Tokens::$commentTokens, true)) {
            $commentEndPointer = CommentHelper::getCommentEndPointer($phpcsFile, $nonWhitespacePointerAfterOpenTag);

            if (StringHelper::endsWith($tokens[$commentEndPointer]['content'], $phpcsFile->eolChar)) {
                $useStatementPlacePointer = $commentEndPointer;
            } else {
                $newLineAfterComment = $commentEndPointer + 1;

                if (\array_key_exists($newLineAfterComment, $tokens) && $tokens[$newLineAfterComment]['content'] === $phpcsFile->eolChar) {
                    $pointerAfterCommentEnd = TokenHelper::findNextExcluding($phpcsFile, \T_WHITESPACE, $newLineAfterComment + 1);

                    //@phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
                    if (TokenHelper::findNextContent($phpcsFile, \T_WHITESPACE, $phpcsFile->eolChar, $newLineAfterComment + 1, $pointerAfterCommentEnd) !== null) {
                        $useStatementPlacePointer = $commentEndPointer;
                    }
                }
            }
        }

        $pointerAfter = TokenHelper::findNextEffective($phpcsFile, $useStatementPlacePointer + 1);

        if ($tokens[$pointerAfter]['code'] === \T_DECLARE) {
            return TokenHelper::findNext($phpcsFile, \T_SEMICOLON, $pointerAfter + 1);
        }

        return $useStatementPlacePointer;
    }

    private function getReferences(\PHP_CodeSniffer\Files\File $phpcsFile, int $openTagPointer) : array
    {
        $references = [];

        foreach (ReferencedNameHelper::getAllReferencedNames($phpcsFile, $openTagPointer) as $referencedName) {
            $reference = new \stdClass();
            $reference->source = self::SOURCE_CODE;
            $reference->name = $referencedName->getNameAsReferencedInFile();
            $reference->type = $referencedName->getType();
            $reference->startPointer = $referencedName->getStartPointer();
            $reference->endPointer = $referencedName->getEndPointer();
            $reference->isClass = $referencedName->isClass();
            $reference->isConstant = $referencedName->isConstant();
            $reference->isFunction = $referencedName->isFunction();

            $references[] = $reference;
        }

        foreach (ReferencedNameHelper::getAllReferencedNamesInAttributes($phpcsFile, $openTagPointer) as $referencedName) {
            $reference = new \stdClass();
            $reference->source = self::SOURCE_ATTRIBUTE;
            $reference->name = $referencedName->getNameAsReferencedInFile();
            $reference->type = $referencedName->getType();
            $reference->startPointer = $referencedName->getStartPointer();
            $reference->endPointer = $referencedName->getEndPointer();
            $reference->isClass = $referencedName->isClass();
            $reference->isConstant = $referencedName->isConstant();
            $reference->isFunction = $referencedName->isFunction();

            $references[] = $reference;
        }

        return $references;
    }

    private function getUniqueNameFromNamespace(string $first, string $second) : array
    {
        $firstSplit = \explode('\\', \ltrim($first, '\\'));
        $secondSplit = \explode('\\', \ltrim($second, '\\'));

        $i = 0;
        $toUse = null;

        foreach ($firstSplit as $value) {
            if (!isset($secondSplit[$i])) {
                break;
            }

            if (\substr($value, 0, 1) !== \substr($secondSplit[$i], 0, 1)) {
                $toUse = \substr($value, 0, 1);

                break;
            }

            $i++;
        }

        $unqualifiedName = NamespaceHelper::getUnqualifiedNameFromFullyQualifiedName($first);

        return $toUse === null
            ? [$unqualifiedName, false]
            : [$toUse . $unqualifiedName, true];
    }

    private function getNormalizedClassName(string $name, array $useStatements, File $phpcsFile) : array
    {
        $unqualifiedName = NamespaceHelper::getUnqualifiedNameFromFullyQualifiedName($name);
        $className = ClassHelper::getName($phpcsFile, TokenHelper::findNext($phpcsFile, [\T_CLASS, \T_INTERFACE, \T_TRAIT], 0));

        if ($className === $unqualifiedName) {
            return $this->getUniqueNameFromNamespace(
                $name,
                ClassHelper::getFullyQualifiedName($phpcsFile, TokenHelper::findNext($phpcsFile, \T_CLASS, 0)),
            );
        }

        foreach ($useStatements as $useStatement) {
            $useStatementUnqualified = NamespaceHelper::getUnqualifiedNameFromFullyQualifiedName($useStatement->getFullyQualifiedTypeName());

            if ($unqualifiedName !== $useStatementUnqualified) {
                continue;
            }

            return $this->getUniqueNameFromNamespace(
                $name,
                $useStatement->getFullyQualifiedTypeName(),
            );
        }

        return [$unqualifiedName, false];
    }
}
