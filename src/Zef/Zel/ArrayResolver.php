<?php declare(strict_types=1);

namespace Zef\Zel;

class ArrayResolver extends AbstractResolver implements \ArrayAccess
{
	/**
	 * @var array
	 */
	private $_data;

	public function __construct($data)
	{
		$this->_data = $data;
	}
	
	public function getValues()
	{
		$data	=	array();
		foreach ( $this->_data as $key=>$val) {
			$data[$key]	=	$this->_fixValue( $val);
		}
		return $data;
	}
	
	public function get()
	{
		return $this->_data;
	}
	
	public function __get( $name) {
		if ( isset( $this->_data[$name])) {
			return $this->_fixValue( $this->_data[$name]);
		}
	}

	public function keys()
	{
		return array_keys($this->_data);
	}

	public function count()
	{
		return count($this->_data);
	}
	
	// ArrayAccess
	
	public function offsetGet( $offset)
	{
	    return isset( $this->_data[$offset]) ? $this->_data[$offset] : null;
	}
	
	public function offsetExists( $offset)
	{
	    return isset( $this->_data[$offset]);
	}
	
	public function offsetUnset( $offset)
	{
	    unset( $this->_data[$offset]);
	}
	
	public function offsetSet( $offset, $value)
	{
	    $this->_data[$offset]  =   $value;
	}
	
	// UTIL
	public function __toString()
	{
		return get_class( $this).'['.count( $this->_data).']';
	}


}