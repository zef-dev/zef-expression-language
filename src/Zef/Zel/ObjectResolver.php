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
		return $this->_object;
	}
	
	public function __get( $name) {
		
		if (property_exists($this->_object, $name)) {
			return $this->_fixValue($this->_object->$name);
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