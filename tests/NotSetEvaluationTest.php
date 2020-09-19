<?php

use PHPUnit\Framework\TestCase;

use Zef\Zel\Symfony\ExpressionLanguage;
use Zef\Zel\ArrayResolver;

class NotSetEvaluationTest extends TestCase
{
    /**
     * @dataProvider resolveToNullProvider
     */
    public function testResolveToNull( $expression, array $values, $expected)
    {
        $expressionLanguage =   new ExpressionLanguage();
        $resolver           =   new ArrayResolver( $values);
        $values             =   $resolver->getValues();
        
        $this->assertEquals( $expected, $expressionLanguage->evaluate( $expression, $values));
    }
    
    public function resolveToNullProvider()
    {
        return [
            ['myvar', [], null],
            ['!myvar', [], true],
            ['myvar || true', [], true],
            ['myarr.value', ['myarr' => []], null],
            ['!myarr.value', ['myarr' => []], true],
            ['myarr.value', ['myarr' => ['value' => true]], true],
            ['!myarr.value', ['myarr' => ['value' => true]], false],
            ['myarr.value', ['myarr' => ['valuex' => true]], null],
            ['!myarr.value', ['myarr' => ['valuex' => true]], true],
        ];
    }

    /**
     * @dataProvider resolveToNullSymfonyProvider
     */
    public function testResolveToNullSymfony( $expression, array $values, $expected)
    {
        $expressionLanguage =   new ExpressionLanguage();
        
        $this->assertEquals( $expected, $expressionLanguage->evaluate( $expression, $values));
    }

    /**
     * @dataProvider resolveToNullSymfonyProvider
     */
    public function testResolveToNullSymfonyWrapped( $expression, array $values, $expected)
    {
        $expressionLanguage =   new ExpressionLanguage();
        $resolver           =   new ArrayResolver( $values);
        $values             =   $resolver->getValues();
        
        $this->assertEquals( $expected, $expressionLanguage->evaluate( $expression, $values));
    }
    
    public function resolveToNullSymfonyProvider()
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