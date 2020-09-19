# ZEF Expression Language

A simple extension to the [Symfony Expression Language](https://github.com/symfony/expression-language) which enables more [JUEL](http://juel.sourceforge.net) like behaviour.

* it will not break on non existing values - it evalueates to `null`
* it enables you tu use JUEL style accessing to array values and object methods (dot notation)


## Installation

```bash
# With composer
$ composer install zef-dev/zef-expression-language
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

To gain JUEL like evaluation addon, warp your values array in `ArrayResolver`.

```php

use Zef\Zel\Symfony\ExpressionLanguage;
use Zef\Zel\ArrayResolver;

$expressionLanguage =   new ExpressionLanguage();
$resolver           =   new ArrayResolver([]);
$evaluated          =   $expressionLanguage->evaluate( 'true', $resolver->getValues());

// now you can access array fields in dot notation
$resolver           =   new ArrayResolver(['myvar'=>['myfield'=>true]]);
$evaluated          =   $expressionLanguage->evaluate( 'myvar.myfield', $resolver->getValues());

// now you can access getters in shorten way
$obj    =   new Myclass();
$obj->getName();
$obj->isValid();
$obj->hasErrror();

$resolver           =   new ArrayResolver(['myvar'=>$obj]);
$evaluated          =   $expressionLanguage->evaluate( 'myvar.name', $resolver->getValues());
$evaluated          =   $expressionLanguage->evaluate( 'myvar.valid', $resolver->getValues());
$evaluated          =   $expressionLanguage->evaluate( 'myvar.error', $resolver->getValues());

        
```
