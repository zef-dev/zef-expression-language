<?php

use PHPUnit\Framework\TestCase;

use Zef\Zel\Symfony\ExpressionLanguage;

class SymfonyEvaluationTest extends TestCase
{
    public function testConstantFunction()
    {
        $expressionLanguage = new ExpressionLanguage();
        $this->assertEquals(\PHP_VERSION, $expressionLanguage->evaluate('constant("PHP_VERSION")'));
        
        $expressionLanguage = new ExpressionLanguage();
        $this->assertEquals('\constant("PHP_VERSION")', $expressionLanguage->compile('constant("PHP_VERSION")'));
    }
    
    public function testStrictEquality()
    {
        $expressionLanguage = new ExpressionLanguage();
        $expression = '123 === a';
        $result = $expressionLanguage->compile($expression, ['a']);
        $this->assertSame('(123 === $a)', $result);
    }
    
    public function testOperatorCollisions()
    {
        $expressionLanguage = new ExpressionLanguage();
        $expression = 'foo.not in [bar]';
        $compiled = $expressionLanguage->compile($expression, ['foo', 'bar']);
        $this->assertSame('in_array($foo->not, [0 => $bar])', $compiled);
        
        $result = $expressionLanguage->evaluate($expression, ['foo' => (object) ['not' => 'test'], 'bar' => 'test']);
        $this->assertTrue($result);
    }
    
    public function testCallBadCallable()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessageMatches('/Unable to call method "\w+" of object "\w+"./');
        $el = new ExpressionLanguage();
        $el->evaluate('foo.myfunction()', ['foo' => new \stdClass()]);
    }
    
    /**
     * @dataProvider shortCircuitProviderEvaluate
     */
    public function testShortCircuitOperatorsEvaluate( $expression, array $values, $expected)
    {
        $expressionLanguage = new ExpressionLanguage();
        $this->assertEquals( $expected, $expressionLanguage->evaluate($expression, $values));
    }
    
    public function shortCircuitProviderEvaluate()
    {
        $object = $this->getMockBuilder('stdClass')->setMethods(['foo'])->getMock();
        $object->expects($this->never())->method('foo');
        
        return [
            ['false and object.foo()', ['object' => $object], false],
            ['false && object.foo()', ['object' => $object], false],
            ['true || object.foo()', ['object' => $object], true],
            ['true or object.foo()', ['object' => $object], true],
        ];
    }


}