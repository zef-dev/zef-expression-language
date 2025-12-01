<?php declare(strict_types=1);

namespace Zef\Zel;

class ObjectResolver extends AbstractResolver
{
	/**
	 * @var \stdClass
	 */
	private $_object;

	public function __construct($object)
	{
		$this->_object	=	$object;
	}
	
	public function keys()
	{
		// TODO: implement properly
		return [];
	}

	public function count()
	{
		// TODO: implement properly
		return 0;
	}

	public function get()
	{
	    if ( !func_num_args()) {
	        return $this->_object;
	    }
	    return $this->__call( 'get', func_get_args());
	}
	
	public function __get( $name) {
        // Only directly access *public* properties. get_object_vars() returns public
        // properties in the current scope, which avoids trying to read private/protected
        // properties on foreign objects (e.g. PSR-7 implementations).
        $publicProps = get_object_vars($this->_object);
        if (array_key_exists($name, $publicProps)) {
            return $this->_fixValue($publicProps[$name]);
        }

		$variants	=	$this->_getVariants( $name);
		
		foreach ( $variants as $variant) 
		{
			if ( method_exists( $this->_object, $variant)) 
			{
				$value	=		$this->_object->$variant();
				return $this->_fixValue( $value);
			}
		}
	}

	public function __call($name, $arguments)
	{
		if (method_exists($this->_object, $name)) {
			return $this->_fixValue($this->_object->$name(...$arguments));
		}
	}
	
	private function _getVariants( $name) {
		$name		=	ucfirst( $name);
		return ['get'.$name, 'is'.$name, 'has'.$name];
	}
	
	// UTIL
	public function __toString()
	{
		return get_class( $this).'';
	}
}