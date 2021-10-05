<?php

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use Zef\Zel\Symfony\ExpressionLanguage;
use Zef\Zel\ArrayResolver;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Zef\Zel\ObjectResolver;

class CorrectEvaluationTest extends TestCase
{
    /**
     * @dataProvider provideSimpleValues
     * @dataProvider provideArrayValues
     * @dataProvider provideJuelArrayValues
     * @dataProvider provideObjectProps
     * @dataProvider provideJuelObjectMethods
     * @dataProvider provideObjectsAndFunctions
     * @dataProvider provideObjectMethods
     */
    public function testResolveWrapped( $expression, array $values, $expected)
    {
        $provider           =   new class() implements ExpressionFunctionProviderInterface {
            public function getFunctions()
            {
                $functions    =   [];
                $functions[] = ExpressionFunction::fromPhp( 'stripos');
                return $functions;
            }
        };
        
        $expressionLanguage =   new ExpressionLanguage( null, [$provider]);
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
    
    public function provideObjectsAndFunctions()
    {
        $child          =   new stdClass();
        $child->value   =   true;
        $child->title   =   'The man who sold the world';
        
        return [
            ['myobj.title', ['myobj' => $child], 'The man who sold the world'],
            ['stripos( myobj.title, \'The\')', ['myobj' => $child], 0],
            ['stripos( myobj.title, \'Gle\')', ['myobj' => $child], false],
            ['stripos( myobj.title, \'The\') !== false ? \'found\' : \'not\'', ['myobj' => $child], 'found'],
            ['stripos( myobj.title, \'Gle\') !== false ? \'found\' : \'not\'', ['myobj' => $child], 'not'],
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

    public function provideObjectMethods()
    {
        $child = new class() {
            public function getName() { return 'Doofus'; }
        };
        $user = new class($child)
        {
            private $_name = 'Test';

            private $_child;

            public function __construct($child)
            {
                $this->_child = $child;
            }

            public function getName() { return $this->_name; }

            public function getChild() { return $this->_child; }
        };
        

        return [
            ['user.getName()', ['user' => $user], 'Test'],
            ['user.getName()']
        ];
    }
}