<?php declare(strict_types=1);

namespace Zef\Zel\Symfony;


class NameNode extends \Symfony\Component\ExpressionLanguage\Node\NameNode
{
    public function __construct( string $name)
    {
        parent::__construct( $name);
    }
    
    public function evaluate( $functions, $values)
    {
        return $values[$this->attributes['name']] ?? null;
    }
    
}