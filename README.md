# ZEF Expression Language

A simple extension to the [Symfony Expression Language](https://github.com/symfony/expression-language) which enables more [JUEL](http://juel.sourceforge.net) like behaviour.

* it will not break on non existing values - it will evaluate to `null`
* it enables you to use JUEL style accessing to array values and object methods (dot notation)


## Installation

```bash
# With composer
$ composer require zef-dev/zef-expression-language
```


## Usage

Just replace Symfony `ExpressionLanguage` with the Zef one.

```php

use Zef\Zel\Symfony\ExpressionLanguage;

$expressionLanguage =   new ExpressionLanguage();
$evaluated          =   $expressionLanguage->evaluate( 'true', []);

// will not throw an exception any more
$evaluated          =   $expressionLanguage->evaluate( 'myvar', []);
$evaluated          =   $expressionLanguage->evaluate( 'myvar[\'myfield\']', ['myvar'=>[]]);

```

## JUEL like usage

To gain JUEL like evaluation addon, wrap your values array in `ArrayResolver`.

```php

use Zef\Zel\Symfony\ExpressionLanguage;
use Zef\Zel\ArrayResolver;

$expressionLanguage =   new ExpressionLanguage();
$resolver           =   new ArrayResolver([]);
$evaluated          =   $expressionLanguage->evaluate( 'true', $resolver->getValues());

// now you can access array fields in dot notation
$resolver           =   new ArrayResolver(['myvar'=>['myfield'=>true]]);
$evaluated          =   $expressionLanguage->evaluate( 'myvar.myfield', $resolver->getValues());

// now you can access getters in a shorter way
$obj    =   new Myclass();
$obj->getName();
$obj->isValid();
$obj->hasErrror();

$resolver           =   new ArrayResolver(['myvar'=>$obj]);
$evaluated          =   $expressionLanguage->evaluate( 'myvar.name', $resolver->getValues());
$evaluated          =   $expressionLanguage->evaluate( 'myvar.valid', $resolver->getValues());
$evaluated          =   $expressionLanguage->evaluate( 'myvar.error', $resolver->getValues());

        
```

## Note about implementation

We wanted to extend Symfony classes and override just what was necessary, but as the original classes were not written in a manner to allow it easily, we ended up copy/pasting some classes completely just to be able implement few minor modifications.




---
This package is created based on the [PHP Boilerplate](https://github.com/kreait/php-boilerplate)
