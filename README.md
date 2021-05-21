# Infinityloop Coding-Standard

Custom PHP 7.4 ruleset for [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer).

It is designed for PHP 7.4 because of its specific property spacing, which is not plausible without typed properties.

## Features

- 97% PSR12 compatible
- Slevomat rules
    - [Slevomat coding-standard](https://github.com/slevomat/coding-standard)
    - Ruleset includes vast majority of Slevomat sniffs as they're great extension of PSR12 with wider scope and stricter requirements.
- Custom rules
    - Package also introduces its own sniffs with more additional checks.

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

### PHPCS sniffs - PSR12 ruleset

We use PHP_Codesniffers predefined PSR12 ruleset, but some slight modifications were made. Few sniffs are replaced with more generic/configurable ones and some are configured to achieve different result.

- `declare(strict_types = 1);` instead of PSR's `declare(strict_types=1);` 
    - one space around `=` operator
- `function abc($param) : ReturnType` instead of PSR's `function abc($param): ReturnType` 
    - one space before and after colon
- `function($param) use ($use)` instaed of PSR's `function ($param) use ($use)`
    - no space after function keyword
- `use \Abc\Xyz\Class;` instead of PSR's `use Abc\Xyz\Class`
    - Leading backslash in use
    - Although import names must already be fully qualified, we believe that using FQN is more logical here.
    
All other necessary sniffs to enforce remaining PSR12 rules are included.

### Custom sniffs

#### InfinityloopCodingStandard.Classes.FinalClassVisibility :wrench:

When class is final and doesnt extend any other class, it's safe to change visibility of all protected functions/properties to private.

#### InfinityloopCodingStandard.Namespaces.UseDoesStartWithBackslash :wrench:

Inverted `SlevomatCodingStandard.Namespaces.UseDoesNotStartWithBackslash` sniff - require imports to start with backslash.

#### InfinityloopCodingStandard.ControlStructures.RequireMultiLineNullCoalesce :wrench:

Enforces null coalesce operator to be reformatted to new line

#### InfinityloopCodingStandard.ControlStructures.SwitchCommentSpacing :wrench:

Checks that there is a certain number of blank lines between code and comment

### Slevomat sniffs

Detailed list of Slevomat sniffs with configured settings. Some sniffs are not included, either because we dont find them helpful, the are too strict, or collide with their counter-sniff (require/disallow pairs).

#### Functional

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
- SlevomatCodingStandard.Classes.ClassStructure
    - groups: [uses, public constants, protected constants, private constants, public static properties, protected static properties, private static properties, public properties, protected properties, private properties, constructor, static constructors, destructor, public static methods, public methods, protected static methods, protected methods, private static methods, private methods, public abstract methods, protected abstract methods, public static abstract methods, protected static abstract methods, magic methods]
- SlevomatCodingStandard.ControlStructures.AssignmentInCondition
- SlevomatCodingStandard.ControlStructures.DisallowContinueWithoutIntegerOperandInSwitch
- SlevomatCodingStandard.ControlStructures.DisallowEmpty
- SlevomatCodingStandard.ControlStructures.RequireNullCoalesceOperator
- SlevomatCodingStandard.ControlStructures.EarlyExit
    - ignoreStandaloneIfInScope: true
    - ignoreOneLineTrailingIf: true
- SlevomatCodingStandard.ControlStructures.RequireNullCoalesceEqualOperator
- SlevomatCodingStandard.ControlStructures.RequireNullSafeObjectOperator
- SlevomatCodingStandard.Functions.StaticClosure
- SlevomatCodingStandard.Operators.DisallowEqualOperators
- SlevomatCodingStandard.Operators.RequireOnlyStandaloneIncrementAndDecrementOperators
- SlevomatCodingStandard.Operators.RequireCombinedAssignmentOperator

Excluded sniffs:

- SlevomatCodingStandard.Classes.DisallowLateStaticBindingForConstants
- SlevomatCodingStandard.Operators.DisallowIncrementAndDecrementOperators
- SlevomatCodingStandard.TypeHints.ParameterTypeHint
- SlevomatCodingStandard.TypeHints.PropertyTypeHint
- SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingTraversableTypeHintSpecification

#### Cleaning

- SlevomatCodingStandard.Functions.UnusedInheritedVariablePassedToClosure
- SlevomatCodingStandard.Functions.UselessParameterDefaultValue
- SlevomatCodingStandard.Namespaces.UnusedUses
    - searchAnnotations: false
    - ignoredAnnotationNames: false
    - ignoredAnnotations: false
- SlevomatCodingStandard.Namespaces.UseFromSameNamespace
- SlevomatCodingStandard.Namespaces.UselessAlias
- SlevomatCodingStandard.PHP.RequireExplicitAssertion
- SlevomatCodingStandard.PHP.RequireNowdoc
- SlevomatCodingStandard.PHP.UselessParentheses
    - ignoreComplexTernaryConditions: true
- SlevomatCodingStandard.PHP.OptimizedFunctionsWithoutUnpacking
- SlevomatCodingStandard.PHP.UselessSemicolon
- SlevomatCodingStandard.Variables.DuplicateAssignmentToVariable
- SlevomatCodingStandard.Variables.UnusedVariable
    - ignoreUnusedValuesWhenOnlyKeysAreUsedInForeach: true
- SlevomatCodingStandard.Variables.UselessVariable
- SlevomatCodingStandard.Exceptions.DeadCatch
- SlevomatCodingStandard.Exceptions.RequireNonCapturingCatch

Excluded sniffs:

- SlevomatCodingStandard.PHP.DisallowReference
- SlevomatCodingStandard.Functions.DisallowEmptyFunction
- SlevomatCodingStandard.Functions.UnusedParameter

#### Formatting

- SlevomatCodingStandard.Arrays.TrailingArrayComma
    - enableAfterHeredoc: false
- SlevomatCodingStandard.Classes.ModernClassNameReference
- SlevomatCodingStandard.Classes.SuperfluousAbstractClassNaming
- SlevomatCodingStandard.Classes.SuperfluousInterfaceNaming
- SlevomatCodingStandard.Classes.SuperfluousTraitNaming
- SlevomatCodingStandard.Classes.SuperfluousExceptionNaming
- SlevomatCodingStandard.Classes.TraitUseDeclaration
- SlevomatCodingStandard.Classes.TraitUseSpacing
    - linesCountBeforeFirstUse: 0
    - linesCountBetweenUses: 0
    - linesCountAfterLastUse: 1
    - linesCountAfterLastUseWhenLastInClass: 0
- SlevomatCodingStandard.ControlStructures.BlockControlStructureSpacing
    - linesCountBeforeControlStructure: 1
    - linesCountBeforeFirstControlStructure: 0
    - linesCountAfterControlStructure: 1
    - linesCountAfterLastControlStructure: 0
    - controlStructures: [switch, try, if, for, foreach, while]
- SlevomatCodingStandard.ControlStructures.JumpStatementsSpacing
    - allowSingleLineYieldStacking: whether or not to allow multiple yield/yield from statements in a row without blank lines.
    - linesCountBefore: 1
    - linesCountBeforeFirst: 0
    - linesCountAfter: 0
    - linesCountAfterLast: 0
    - linesCountAfterWhenLastInCaseOrDefault: 0
    - linesCountAfterWhenLastInLastCaseOrDefault: 0
    - jumpStatements: [goto, throw, yield, continue, break, return]
- SlevomatCodingStandard.ControlStructures.LanguageConstructWithParentheses
- SlevomatCodingStandard.ControlStructures.NewWithParentheses
- SlevomatCodingStandard.ControlStructures.RequireMultiLineTernaryOperator
    - lineLengthLimit: 0
- SlevomatCodingStandard.ControlStructures.RequireShortTernaryOperator
- SlevomatCodingStandard.ControlStructures.RequireTernaryOperator
    - ignoreMultiLine: false
- SlevomatCodingStandard.ControlStructures.DisallowYodaComparison
- SlevomatCodingStandard.Functions.DisallowArrowFunction
- SlevomatCodingStandard.Functions.RequireTrailingCommaInCall
- SlevomatCodingStandard.Functions.RequireTrailingCommaInDeclaration
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
- SlevomatCodingStandard.TypeHints.LongTypeHints
- SlevomatCodingStandard.TypeHints.NullTypeHintOnLastPosition
- SlevomatCodingStandard.PHP.ShortList
- SlevomatCodingStandard.PHP.TypeCast
- SlevomatCodingStandard.Classes.ClassConstantVisibility
    - fixable: true
- SlevomatCodingStandard.TypeHints.ReturnTypeHintSpacing
    - spacesCountBeforeColon: 1
- SlevomatCodingStandard.TypeHints.NullableTypeForNullDefaultValue
- SlevomatCodingStandard.TypeHints.ParameterTypeHintSpacing
- SlevomatCodingStandard.TypeHints.PropertyTypeHintSpacing
- SlevomatCodingStandard.TypeHints.UnionTypeHintFormat
    - withSpaces: no
    - shortNullable: yes
    - nullPosition: last
- SlevomatCodingStandard.Namespaces.DisallowGroupUse
- SlevomatCodingStandard.Namespaces.FullyQualifiedExceptions
    - specialExceptionNames: false
    - ignoredNames: default
- SlevomatCodingStandard.Namespaces.FullyQualifiedGlobalConstants
    - exclude: false
- SlevomatCodingStandard.Namespaces.FullyQualifiedGlobalFunctions
    - exclude: false
- SlevomatCodingStandard.Namespaces.MultipleUsesPerLine
- SlevomatCodingStandard.Classes.EmptyLinesAroundClassBraces
    - linesCountAfterOpeningBrace: 0
    - linesCountBeforeClosingBrace: 0
- SlevomatCodingStandard.Namespaces.FullyQualifiedClassNameInAnnotation
- SlevomatCodingStandard.Commenting.ForbiddenAnnotations
    - forbiddenAnnotations: @author, @created, @version, @package, @copyright, @license, @throws
- SlevomatCodingStandard.Classes.RequireConstructorPropertyPromotion
- SlevomatCodingStandard.Commenting.EmptyComment
- SlevomatCodingStandard.Commenting.InlineDocCommentDeclaration
- SlevomatCodingStandard.Commenting.UselessFunctionDocComment
    - traversableTypeHints: default
- SlevomatCodingStandard.Commenting.UselessInheritDocComment
- SlevomatCodingStandard.ControlStructures.UselessIfConditionWithReturn
    - assumeAllConditionExpressionsAreAlreadyBoolean: false
- SlevomatCodingStandard.ControlStructures.UselessTernaryOperator
    - assumeAllConditionExpressionsAreAlreadyBoolean: false
- SlevomatCodingStandard.Files.LineLength
    - lineLengthLimit: 150
    - ignoreComments: true
    - ignoreImports: true
- SlevomatCodingStandard.Classes.ParentCallSpacing    
    - linesCountBeforeParentCall: 1
    - linesCountBeforeFirstControlParentCall: 0
    - linesCountAfterParentCall: 1
    - linesCountAfterLastParentCall: 0
- SlevomatCodingStandard.Arrays.MultiLineArrayEndBracketPlacement
    
Excluded sniffs:

- SlevomatCodingStandard.Namespaces.UseDoesNotStartWithBackslash
    - Custom UseDoesStartWithBackslash is used instead.
- SlevomatCodingStandard.ControlStructures.NewWithoutParentheses**
    - NewWithParentheses is used instead.
- SlevomatCodingStandard.ControlStructures.DisallowShortTernaryOperator
    - RequireShortTernaryOperator is used instead.
- SlevomatCodingStandard.ControlStructures.RequireYodaComparison
    - DisallowYodaComparison is used instead.
- SlevomatCodingStandard.Functions.RequireArrowFunction
    - DisallowArrowFunction is used instead.
- SlevomatCodingStandard.Numbers.RequireNumericLiteralSeparator
    - DisallowNumericLiteralSeparator is used instead.
- SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly
- SlevomatCodingStandard.Namespaces.UseOnlyWhitelistedNamespaces
- SlevomatCodingStandard.Commenting.ForbiddenComments
- SlevomatCodingStandard.Commenting.DocCommentSpacing
    - This sniff clashed with some other sniffs and caused some FAILED TO FIX errors.
- SlevomatCodingStandard.Commenting.RequireOneLinePropertyDocComment
    - Property doc comments are replaced by typed proeprties.
- SlevomatCodingStandard.Commenting.DisallowOneLinePropertyDocComment
    - Property doc comments are replaced by typed proeprties.
- SlevomatCodingStandard.Classes.RequireMultiLineMethodSignature
- SlevomatCodingStandard.Classes.RequireSingleLineMethodSignature
- SlevomatCodingStandard.Classes.PropertySpacing
- SlevomatCodingStandard.Classes.ConstantSpacing
- SlevomatCodingStandard.Commenting.RequireOneLineDocComment
- SlevomatCodingStandard.Arrays.SingleLineArrayWhitespace
- SlevomatCodingStandard.Operators.NegationOperatorSpacing
