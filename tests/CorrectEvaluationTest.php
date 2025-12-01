<?php

use PHPUnit\Framework\TestCase;
use Zef\Zel\Symfony\ExpressionLanguage;
use Zef\Zel\ArrayResolver;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
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
     * @dataProvider provideAlsoNonPublicObjectMethods
     */
    public function testResolveWrappedNonPublicProperties( $expression, array $values, $expected)
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
        $resolver           =   new ObjectResolver( $values);
        $values             =   $resolver->get();

        $this->assertEquals( $expected, $expressionLanguage->evaluate( $expression, $values));
    }
    
    
    /**
     * @dataProvider provideWrappedArrayyValues
     */
    public function testResolveWrappedArrays( $expression, $expected)
    {
        $provider           =   new class() implements ExpressionFunctionProviderInterface {
            public function getFunctions()
            {
                $functions    =   [];
                $functions[] = ExpressionFunction::fromPhp( 'json_encode');
                return $functions;
            }
        };
        
        $values =   [
            'temp' => 'something',
            'data' => [
                ['index'=>'a'],
                ['index'=>'b'],
            ]
        ];
        
        $expressionLanguage =   new ExpressionLanguage( null, [$provider]);
        $resolver           =   new ArrayResolver( $values);
        
        $this->assertEquals( $expected, $expressionLanguage->evaluate( $expression, $resolver->getValues()));
    }
    
    public function provideWrappedArrayyValues()
    {
        return [
            ['json_encode( data)', '[{"index":"a"},{"index":"b"}]']
        ];
    }

    public function testResolveWrappedPrivateMethodCall()
    {
        $child = new class() {
            public function greet($name) { return "Hello $name"; }
        };
        $user = new class($child)
        {
            private $test = 'Another Test';

            private $_name = 'Test';

            private $_child;

            public function __construct($child)
            {
                $this->_child = $child;
            }

            private function sayHi() { return 'Hi'; }

            public function getName() { return $this->_name; }

            public function getChild() { return $this->_child; }
        };

        $this->expectException('RuntimeException');
        $el = new ExpressionLanguage();
        $el->evaluate('foo.sayHi()', ['foo' => $user]);
    }

    public function testResolveWrappedPrivatePropertyUsesGetter()
    {
        $request = new class() {
            private $parsedBody = ['foo' => 'bar'];

            public function getParsedBody()
            {
                return $this->parsedBody;
            }
        };

        $el = new ExpressionLanguage();
        $adapter = new ObjectResolver($request);

        $this->assertEquals(
            ['foo' => 'bar'],
            $el->evaluate('request.parsedBody', ['request' => $adapter])
        );
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
            public function greet($name) { return "Hello $name"; }
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
            ['user.get().getName()', ['user' => $user], 'Test'],
            ['user.get().getChild().greet(\'Goofus\')', ['user' => $user], 'Hello Goofus']
        ];
    }

    public function provideAlsoNonPublicObjectMethods()
    {
        $child = new class() {
            public function greet($name) { return "Hello $name"; }
        };
        $user = new class($child)
        {
            private $test = 'Another Test';

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
            ['user.test', ['user' => $user], null]
        ];
    }
}
