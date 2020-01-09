# Infinityloop Coding-Standard

Custom ruleset for [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer).

This standard uses sniffs bundled with PHPCS, numerous sniffs from [Slecomat coding-standard](https://github.com/slevomat/coding-standard), and also some of its own custom built sniffs.

## Installation

Recommended way is to install using [composer-bin plugin](https://github.com/bamarni/composer-bin-plugin), so it wont interfere with your project's dependencies.

```
composer require --dev bamarni/composer-bin-plugin
composer bin phpcs require infinityloop-dev/coding-standard
```

## Standard description

### 97% PSR12 compatible
- this standard encorces `declare(strict_types = 1);` instead of PSR's `declare(strict_types=1);` 
    - one space around `=` operator
- this standard enforces `function abc($param) : ReturnType` instead of PSR's `function abc($param): ReturnType` 
    - one space before and after colon
- this standard enforces `function($param) use ($use)` instaed of PSR's `function ($param) use ($use)`
    - no space after function keyword
    
### Slevomat sniffs

TODO

### Custom sniffs

TODO
