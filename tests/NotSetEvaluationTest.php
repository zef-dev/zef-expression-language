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
     * @dataProvider provideObjectProps
     * @dataProvider provideJuelObjectMethods
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
     * @dataProvider provideObjectProps
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
    
    public function provideObjectProps()
    {
        $child          =   new stdClass();
        $parent         =   new stdClass();
        $parent->child  =   $child;
        $empty_parent   =   new stdClass();
        
        return [
            ['myobj.novalue', ['myobj' => $empty_parent], null],
            ['myobj.child.novalue', ['myobj' => $empty_parent], null],
            ['myobj.child.novalue', ['myobj' => $parent], null],
        ];
    }
    
    public function provideJuelObjectMethods()
    {
        $child          =   new stdClass();
        $parent         =   new stdClass();
        $parent->getChild   =   function () use ( $child) {
            return $child;
        };
        
        $empty_parent   =   new stdClass();
        return [
            ['myobj.novalue', ['myobj' => $empty_parent], null],
            ['myobj.child.novalue', ['myobj' => $empty_parent], null],
            ['myobj.child.novalue', ['myobj' => $parent], null],
        ];
    }


}