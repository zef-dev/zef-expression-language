<?php

use PHPUnit\Framework\TestCase;

use Zef\Zel\Symfony\ExpressionLanguage;
use Zef\Zel\ArrayResolver;

class CorrectEvaluationTest extends TestCase
{
    /**
     * @dataProvider provideSimpleValues
     * @dataProvider provideArrayValues
     * @dataProvider provideJuelArrayValues
     * @dataProvider provideObjectProps
     * @dataProvider provideJuelObjectMethods
     */
    public function testResolveWrapped( $expression, array $values, $expected)
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
    public function testResolveBasic( $expression, array $values, $expected)
    {
        $expressionLanguage =   new ExpressionLanguage();
        $resolver           =   new ArrayResolver( $values);
        $values             =   $resolver->getValues();
        
        $this->assertEquals( $expected, $expressionLanguage->evaluate( $expression, $values));
    }
    
    public function provideSimpleValues()
    {
        return [
            ['myvar', ['myvar'=>true], true],
            ['!myvar', ['myvar'=>true], false],
            ['myvar && false', ['myvar'=>true], false],
        ];
    }

    public function provideJuelArrayValues()
    {
        return [
            ['myarr.value', ['myarr' => ['value' => true]], true],
            ['!myarr.value', ['myarr' => ['value' => true]], false],
            ['myarr.value.value', ['myarr' => ['value' => ['value' => true]]], true],
        ];
    }
    
    public function provideArrayValues()
    {
        return [
            ['myarr[\'value\']', ['myarr' => ['value' => true]], true],
            ['myarr[\'value\'][\'value\']', ['myarr' => ['value' => ['value' => true]]], true],
            ['!myarr[\'value\']', ['myarr' => ['value' => true]], false],
        ];
    }
    
    public function provideObjectProps()
    {
        $child          =   new stdClass();
        $child->value   =   true;
        
        $parent         =   new stdClass();
        $parent->child  =   $child;
        
        return [
            ['myobj.value', ['myobj' => $child], true],
            ['myobj.child.value', ['myobj' => $parent], true],
        ];
    }
    
    public function provideJuelObjectMethods()
    {
        $child = new class () {
            public function getValue()
            {
                return true;
            }
        };
        
        
        $parent = new class ( $child) {
            private $_child;
            public function __construct( $child)
            {
                $this->_child   =   $child;
            }
            public function getChild()
            {
                return $this->_child;
            }
        };
        
        return [
            ['myobj.value', ['myobj' => $child], true],
            ['myobj.child.value', ['myobj' => $parent], true],
        ];
    }


}