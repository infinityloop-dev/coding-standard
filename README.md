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
- ruleset encorces `declare(strict_types = 1);` instead of PSR's `declare(strict_types=1);` 
    - one space around `=` operator
- ruleset enforces `function abc($param) : ReturnType` instead of PSR's `function abc($param): ReturnType` 
    - one space before and after colon
- ruleset enforces `function($param) use ($use)` instaed of PSR's `function ($param) use ($use)`
    - no space after function keyword
    
Ruleset includes all necessary sniffs to enforce remaining PSR12 rules.
    
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

SlevomatCodingStandard.Classes.UnusedPrivateElements construction
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

SlevomatCodingStandard.PHP.DisallowReference

#### Formatting

### Custom sniffs

TODO

## Example class

