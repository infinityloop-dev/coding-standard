# Infinityloop Coding-Standard

Custom ruleset for [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer).

This ruleset uses sniffs bundled with PHPCS, numerous sniffs from [Slecomat coding-standard](https://github.com/slevomat/coding-standard), and also some of its own custom built sniffs.

## Installation

Recommended way is to install using [composer-bin plugin](https://github.com/bamarni/composer-bin-plugin), so it wont interfere with your project's dependencies.

```
composer require --dev bamarni/composer-bin-plugin
composer bin phpcs require infinityloop-dev/coding-standard
```

## Usage

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

## Description

### 97% PSR12 compatible
- this ruleset encorces `declare(strict_types = 1);` instead of PSR's `declare(strict_types=1);` 
    - one space around `=` operator
- this ruleset enforces `function abc($param) : ReturnType` instead of PSR's `function abc($param): ReturnType` 
    - one space before and after colon
- this ruleset enforces `function($param) use ($use)` instaed of PSR's `function ($param) use ($use)`
    - no space after function keyword
    
Ruleset includes all necessary sniffs to enforce remaining PSR12 rules.
    
### Slevomat sniffs

TODO

### Custom sniffs

TODO

## Example class

