<?php

use PHPUnit\Framework\TestCase;
use Zef\Zel\Symfony\ExpressionLanguage;
use Zef\Zel\ArrayResolver;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class ValueAdaptersTest extends TestCase
{
    /**
     * @dataProvider provideSimpleValues
     */
    public function testResolveWrapped( $expression, array $values, $expected)
    {
        $provider           =   new class() implements ExpressionFunctionProviderInterface {
            public function getFunctions()
            {
                $functions    =   [];
                $functions[] = ExpressionFunction::fromPhp( 'array_keys');
                $functions[] = new ExpressionFunction(
                    'test_custom',
                    function ( $arr) {
                        return sprintf( 'test_custom(%1)', $arr);
                    },
                    function( $args, $arr) {
                        return array_keys( $arr);
                    }
                    );
                return $functions;
            }
        };
        
        $expressionLanguage =   new ExpressionLanguage( null, [$provider]);
        
        $resolver           =   new ArrayResolver( $values);
        $values             =   $resolver->getValues();
        
        $ret = $expressionLanguage->evaluate( $expression, $values);
        
        $this->assertEquals( $expected, $ret);
    }
    
    public function provideSimpleValues()
    {
        $inner = [
            'field_1_1' => 2,
            'field_1_2' => 3,
        ];
        
        $values = [
            'arr' => [
                'field_1' => 1,
                'field_2' => $inner,
            ]
        ];
        
        return [
            ['arr.field_2', $values, $inner],
            ['array_keys( arr.field_2)', $values, ['field_1_1', 'field_1_2']],
            ['test_custom( arr.field_2)', $values, ['field_1_1', 'field_1_2']],
            ['array_keys( arr["field_2"])', $values, ['field_1_1', 'field_1_2']],
            ['test_custom( arr["field_2"])', $values, ['field_1_1', 'field_1_2']],
        ];
    }
}
