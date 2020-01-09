# Infinityloop Coding-Standard

Custom PHP 7.4 ruleset for [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer).

Ruleset uses sniffs bundled with PHPCS, numerous sniffs from [Slecomat coding-standard](https://github.com/slevomat/coding-standard), and also some of its own custom built sniffs.

Ruleset is designed for PHP 7.4 because of its specific property spacing, which is not plausible without typed properties.

## Installation

Recommended way is to install using [composer-bin plugin](https://github.com/bamarni/composer-bin-plugin), so it won't interfere with your project's dependencies.

```
composer require --dev bamarni/composer-bin-plugin
composer bin phpcs require infinityloop-dev/coding-standard
```

## Usage

Running phpcs out of the box:

```
// phpcs:
php vendor/bin/phpcs\
    --parallel=4\
    --standard=vendor-bin/phpcs/vendor/infinityloop-dev/coding-standard/InfinityloopCodingStandard/ruleset.xml\
    --extensions=php\
    app tests

// phpcbf:
php vendor/bin/phpcbf\
    --parallel=4\
    --standard=vendor-bin/phpcs/vendor/infinityloop-dev/coding-standard/InfinityloopCodingStandard/ruleset.xml\
    --extensions=php\
    app tests
```

Feel free to copy ruleset.xml in your project directory, customize configuration and create your own standard with this ruleset as its base.

## Description

### 97% PSR12 compatible
- `declare(strict_types = 1);` instead of PSR's `declare(strict_types=1);` 
    - one space around `=` operator
- `function abc($param) : ReturnType` instead of PSR's `function abc($param): ReturnType` 
    - one space before and after colon
- `function($param) use ($use)` instaed of PSR's `function ($param) use ($use)`
    - no space after function keyword
    
Ruleset includes all necessary sniffs to enforce remaining PSR12 rules.

### PHPCS sniffs

### Slevomat sniffs

#### Functional

- SlevomatCodingStandard.TypeHints.ParameterTypeHint
    - enableObjectTypeHint: true
    - traversableTypeHints: false
- SlevomatCodingStandard.TypeHints.PropertyTypeHint
    - enableNativeTypeHint: true
    - traversableTypeHints: false
- SlevomatCodingStandard.TypeHints.ReturnTypeHint
    - enableObjectTypeHint: true
    - traversableTypeHints: false
- SlevomatCodingStandard.TypeHints.UselessConstantTypeHint
- SlevomatCodingStandard.Exceptions.ReferenceThrowableOnly
- SlevomatCodingStandard.TypeHints.DeclareStrictTypes
    - newlinesCountBetweenOpenTagAndDeclare: 2
    - newlinesCountAfterDeclare: 2
    - spacesCountAroundEqualsSign: 1
- SlevomatCodingStandard.Arrays.DisallowImplicitArrayCreation
- SlevomatCodingStandard.Classes.UselessLateStaticBinding
- SlevomatCodingStandard.ControlStructures.AssignmentInCondition
- SlevomatCodingStandard.ControlStructures.DisallowContinueWithoutIntegerOperandInSwitch
- SlevomatCodingStandard.ControlStructures.DisallowEmpty
- SlevomatCodingStandard.ControlStructures.RequireNullCoalesceOperator
- SlevomatCodingStandard.ControlStructures.EarlyExit
    - ignoreStandaloneIfInScope: true
- SlevomatCodingStandard.Functions.StaticClosure
- SlevomatCodingStandard.Operators.DisallowEqualOperators
- SlevomatCodingStandard.Operators.RequireOnlyStandaloneIncrementAndDecrementOperators
- SlevomatCodingStandard.Operators.RequireCombinedAssignmentOperator

Excluded sniffs:

- SlevomatCodingStandard.Classes.DisallowLateStaticBindingForConstants
- SlevomatCodingStandard.Operators.DisallowIncrementAndDecrementOperators

#### Cleaning

- SlevomatCodingStandard.Classes.UnusedPrivateElements construction
    - alwaysUsedPropertiesAnnotations: false
    - alwaysUsedPropertiesSuffixes: false
- SlevomatCodingStandard.Functions.UnusedInheritedVariablePassedToClosure
- SlevomatCodingStandard.Functions.UnusedParameter
- SlevomatCodingStandard.Functions.UselessParameterDefaultValue
- SlevomatCodingStandard.Namespaces.UnusedUses
    - searchAnnotations: false
    - ignoredAnnotationNames: false
    - ignoredAnnotations: false
- SlevomatCodingStandard.Namespaces.UseFromSameNamespace
- SlevomatCodingStandard.Namespaces.UselessAlias
- SlevomatCodingStandard.PHP.RequireExplicitAssertion
- SlevomatCodingStandard.PHP.RequireNowdoc wrench
- SlevomatCodingStandard.PHP.UselessParentheses
    - ignoreComplexTernaryConditions: true
- SlevomatCodingStandard.PHP.OptimizedFunctionsWithoutUnpacking
- SlevomatCodingStandard.PHP.UselessSemicolon
- SlevomatCodingStandard.Variables.DuplicateAssignmentToVariable
- SlevomatCodingStandard.Variables.UnusedVariable
    - ignoreUnusedValuesWhenOnlyKeysAreUsedInForeach: true
- SlevomatCodingStandard.Variables.UselessVariable
- SlevomatCodingStandard.Exceptions.DeadCatch

Excluded sniffs:

- SlevomatCodingStandard.PHP.DisallowReference

#### Formatting

- SlevomatCodingStandard.Arrays.TrailingArrayComma wrench
    - enableAfterHeredoc: false
- SlevomatCodingStandard.Classes.ModernClassNameReference wrench
- SlevomatCodingStandard.Classes.SuperfluousAbstractClassNaming
- SlevomatCodingStandard.Classes.SuperfluousInterfaceNaming
- SlevomatCodingStandard.Classes.SuperfluousTraitNaming
- SlevomatCodingStandard.Classes.TraitUseDeclaration
- SlevomatCodingStandard.Classes.TraitUseSpacing wrench
    - linesCountBeforeFirstUse: 0
    - linesCountBetweenUses: 0
    - linesCountAfterLastUse: 1
    - linesCountAfterLastUseWhenLastInClass: 0
- SlevomatCodingStandard.ControlStructures.BlockControlStructureSpacing
    - linesCountBeforeControlStructure: 1
    - linesCountBeforeFirstControlStructure: 0
    - linesCountAfterControlStructure: 1
    - linesCountAfterLastControlStructure: 0
    - tokensToCheck: default
- SlevomatCodingStandard.ControlStructures.JumpStatementsSpacing
    - allowSingleLineYieldStacking: whether or not to allow multiple yield/yield from statements in a row without blank lines.
    - linesCountBeforeControlStructure: 1
    - linesCountBeforeFirstControlStructure: 0
    - linesCountAfterControlStructure: 0
    - linesCountAfterLastControlStructure: 0
    - tokensToCheck: default
- SlevomatCodingStandard.ControlStructures.LanguageConstructWithParentheses
- SlevomatCodingStandard.ControlStructures.NewWithParentheses
- SlevomatCodingStandard.ControlStructures.RequireMultiLineTernaryOperator
    - lineLengthLimit: 0
- SlevomatCodingStandard.ControlStructures.RequireShortTernaryOperator
- SlevomatCodingStandard.ControlStructures.RequireTernaryOperator
    - ignoreMultiLine: false
- SlevomatCodingStandard.ControlStructures.DisallowYodaComparison
- SlevomatCodingStandard.Functions.DisallowArrowFunction
- SlevomatCodingStandard.Functions.TrailingCommaInCall
- SlevomatCodingStandard.Namespaces.AlphabeticallySortedUses
    - psr12Compatible: true
    - caseSensitive: true
- SlevomatCodingStandard.Namespaces.RequireOneNamespaceInFile
- SlevomatCodingStandard.Namespaces.NamespaceDeclaration
- SlevomatCodingStandard.Namespaces.NamespaceSpacing
    - linesCountBeforeNamespace: 1
    - linesCountAfterNamespace: 1
- SlevomatCodingStandard.Namespaces.UseSpacing
    - linesCountBeforeFirstUse: 1
    - linesCountBetweenUseTypes: 0
    - linesCountAfterLastUse: 1
- SlevomatCodingStandard.Numbers.DisallowNumericLiteralSeparator
- SlevomatCodingStandard.PHP.ReferenceSpacing
    - spacesCountAfterReference: 0
- SlevomatCodingStandard.Operators.SpreadOperatorSpacing
    - spacesCountAfterOperator: 0
- SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax
    - traversableTypeHints: default
- SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint
- SlevomatCodingStandard.TypeHints.LongTypeHints wrench
- SlevomatCodingStandard.TypeHints.NullTypeHintOnLastPosition
- SlevomatCodingStandard.PHP.ShortList wrench
- SlevomatCodingStandard.PHP.TypeCast
- SlevomatCodingStandard.Files.TypeNameMatchesFileName
- SlevomatCodingStandard.Classes.ClassConstantVisibility
    - fixable: true
- SlevomatCodingStandard.TypeHints.ReturnTypeHintSpacing
    - spacesCountBeforeColon: 1
- SlevomatCodingStandard.TypeHints.NullableTypeForNullDefaultValue
- SlevomatCodingStandard.TypeHints.ParameterTypeHintSpacing wrench
- SlevomatCodingStandard.TypeHints.PropertyTypeHintSpacing wrench
- SlevomatCodingStandard.Namespaces.DisallowGroupUse
- SlevomatCodingStandard.Namespaces.FullyQualifiedClassNameAfterKeyword
    - keywordsToCheck: T_EXTENDS, T_IMPLEMENETS, T_USE, T_NEW, T_THROW
- SlevomatCodingStandard.Namespaces.FullyQualifiedExceptions
    - specialExceptionNames: false
    - ignoredNames: default
- SlevomatCodingStandard.Namespaces.FullyQualifiedGlobalConstants
    - exclude: false
- SlevomatCodingStandard.Namespaces.FullyQualifiedGlobalFunctions
    - exclude: false
- SlevomatCodingStandard.Namespaces.MultipleUsesPerLine
- SlevomatCodingStandard.Namespaces.UseDoesNotStartWithBackslash wrench
- SlevomatCodingStandard.Classes.EmptyLinesAroundClassBraces wrench
    - linesCountAfterOpeningBrace: 0
    - linesCountBeforeClosingBrace: 0
- SlevomatCodingStandard.Namespaces.FullyQualifiedClassNameInAnnotation wrench
- SlevomatCodingStandard.Commenting.ForbiddenAnnotations
    - forbiddenAnnotations: @author, @created, @version, @package, @copyright, @license, @throws
- SlevomatCodingStandard.Commenting.EmptyComment
- SlevomatCodingStandard.Commenting.InlineDocCommentDeclaration wrench
- SlevomatCodingStandard.Commenting.UselessFunctionDocComment
    - traversableTypeHints: default
- SlevomatCodingStandard.Commenting.UselessInheritDocComment
- SlevomatCodingStandard.ControlStructures.UselessIfConditionWithReturn
    - assumeAllConditionExpressionsAreAlreadyBoolean: false
- SlevomatCodingStandard.ControlStructures.UselessTernaryOperator
    - assumeAllConditionExpressionsAreAlreadyBoolean: false

Excluded sniffs:

- SlevomatCodingStandard.Classes.SuperfluousExceptionNaming
- SlevomatCodingStandard.ControlStructures.NewWithoutParentheses
- SlevomatCodingStandard.ControlStructures.DisallowShortTernaryOperator
- SlevomatCodingStandard.ControlStructures.RequireYodaComparison
- SlevomatCodingStandard.Functions.RequireArrowFunction
- SlevomatCodingStandard.Numbers.RequireNumericLiteralSeparator
- SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly
- SlevomatCodingStandard.Namespaces.UseOnlyWhitelistedNamespaces
- SlevomatCodingStandard.Commenting.ForbiddenComments
- SlevomatCodingStandard.Commenting.DocCommentSpacing
- SlevomatCodingStandard.Commenting.RequireOneLinePropertyDocComment
- SlevomatCodingStandard.Commenting.DisallowOneLinePropertyDocComment

### Custom sniffs

TODO

## Example class

