<?php declare(strict_types=1);

namespace Zef\Zel\Symfony;


class GetAttrNode extends \Symfony\Component\ExpressionLanguage\Node\GetAttrNode
{
    
    public function evaluate( $functions, $values)
    {
        switch ($this->attributes['type']) {
            case self::PROPERTY_CALL:
                $obj = $this->nodes['node']->evaluate($functions, $values);
                if ( is_null( $obj)) {
                    return null;    
                }
                
                if (empty($obj)) {
                    return null;  
                }
                
                if (!\is_object($obj)) {
                    throw new \RuntimeException('Unable to get a property on a non-object. ['.gettype( $obj).']');
                }
                
                $property = $this->nodes['attribute']->attributes['value'];
                
                return $obj->$property;
                
            case self::METHOD_CALL:
                $obj = $this->nodes['node']->evaluate($functions, $values);
                
                if ( is_null( $obj)) {
                    return null;
                }
                
                if (!\is_object($obj)) {
                    throw new \RuntimeException('Unable to get a property on a non-object.');
                }
                if (!\is_callable($toCall = [$obj, $this->nodes['attribute']->attributes['value']])) {
                    throw new \RuntimeException(sprintf('Unable to call method "%s" of object "%s".', $this->nodes['attribute']->attributes['value'], \get_class($obj)));
                }
                
                return $toCall(...array_values($this->nodes['arguments']->evaluate($functions, $values)));
                
            case self::ARRAY_CALL:
                $array = $this->nodes['node']->evaluate($functions, $values);
                
                if ( is_null( $array)) {
                    return null;
                }
                
                if (!\is_array($array) && !$array instanceof \ArrayAccess) {
                    throw new \RuntimeException('Unable to get an item on a non-array.');
                }
                
                return $array[$this->nodes['attribute']->evaluate($functions, $values)] ?? null;
        }
    }
    
}