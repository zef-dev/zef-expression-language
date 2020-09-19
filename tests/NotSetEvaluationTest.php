<?php

use PHPUnit\Framework\TestCase;

use Zef\Zel\Symfony\ExpressionLanguage;
use Zef\Zel\ArrayResolver;

class NotSetEvaluationTest extends TestCase
{
    /**
     * @dataProvider provideSimpleValues
     * @dataProvider provideArrayValues
     * @dataProvider provideJuelArrayValues
     */
    public function testResolveToNullWrapped( $expression, array $values, $expected)
    {
        $expressionLanguage =   new ExpressionLanguage();
        $resolver           =   new ArrayResolver( $values);
        $values             =   $resolver->getValues();
        
        $this->assertEquals( $expected, $expressionLanguage->evaluate( $expression, $values));
    }

    /**
     * @dataProvider provideSimpleValues
     * @dataProvider provideArrayValues
     */
    public function testResolveToNullBasic( $expression, array $values, $expected)
    {
        $expressionLanguage =   new ExpressionLanguage();
        $resolver           =   new ArrayResolver( $values);
        $values             =   $resolver->getValues();
        
        $this->assertEquals( $expected, $expressionLanguage->evaluate( $expression, $values));
    }
    
    public function provideSimpleValues()
    {
        return [
            ['myvar', [], null],
            ['!myvar', [], true],
            ['myvar || true', [], true],
        ];
    }

    public function provideJuelArrayValues()
    {
        return [
            ['myarr.value', ['myarr' => []], null],
            ['!myarr.value', ['myarr' => []], true],
            ['myarr.value', ['myarr' => ['value' => true]], true],
            ['!myarr.value', ['myarr' => ['value' => true]], false],
            ['myarr.value', ['myarr' => ['valuex' => true]], null],
            ['!myarr.value', ['myarr' => ['valuex' => true]], true],
        ];
    }
    
    public function provideArrayValues()
    {
        return [
            ['myvar', [], null],
            ['!myvar', [], true],
            ['myvar || true', [], true],
            ['myarr[\'value\']', ['myarr' => []], null],
            ['myarr[\'value\'][\'value2\']', ['myarr' => []], null],
            ['!myarr[\'value\']', ['myarr' => []], true],
        ];
    }


}