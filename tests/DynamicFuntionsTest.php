<?php

use PHPUnit\Framework\TestCase;
use Zef\Zel\Symfony\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;

class DynamicFuntionsTest extends TestCase
{

    // this one is generated by ChatGPT
    public function testDynamicFunctionRegistration()
    {
        $language = new ExpressionLanguage();

        // Evaluate a simple expression
        $this->assertEquals(3, $language->evaluate('1 + 2'));

        // Dynamically add a new function
        $language->register(
            'double',
            function ($value) {
                return sprintf('(%s * 2)', $value);
            },
            function ($args, $value) {
                return $value * 2;
            }
        );

        // Use the new function
        $this->assertEquals(10, $language->evaluate('double(5)'));
    }

    public function testNamedFunctionsExecution()
    {
        $context = [
            'name' => 'Pero',
            'text' => 'Hello World',
        ];

        $provider           =   new class() implements ExpressionFunctionProviderInterface {
            public function getFunctions()
            {
                $functions    =   [];
                $functions[] = ExpressionFunction::fromPhp('stripos');
                return $functions;
            }
        };

        $expressionLanguage =   new ExpressionLanguage(null, [$provider]);

        // INITIAL EVAL
        $expression = 'text';
        $expected = 'Hello World';
        $this->assertEquals($expected, $expressionLanguage->evaluate($expression, $context));

        // REGISTER FUNCTION
        $function = function ($text) {
            return strtoupper($text);
        };
        $expressionLanguage->addFunction(new \Symfony\Component\ExpressionLanguage\ExpressionFunction(
            'upper',
            function () {
                return ''; // No-op for compilation
            },
            function (...$params) use ($function) {
                array_shift($params);
                return call_user_func_array($function, $params);
            }
        ));

        // USE FUNCTION
        $expression = 'upper( "Hello World" )';
        $expected = 'HELLO WORLD';
        $this->assertEquals($expected, $expressionLanguage->evaluate($expression, $context));
    }
}
