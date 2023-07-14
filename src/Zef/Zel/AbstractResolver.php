<?php declare(strict_types=1);

namespace Zef\Zel;

use Countable;

abstract class AbstractResolver implements IValueAdapter, Countable
{

	abstract public function keys();

	abstract public function count();

	protected function _fixValue( $value) {

	    if ( is_array( $value) && !self::isArrayIndexed( $value)) {
			$rsv = new ArrayResolver( $value);
			return $rsv;
		}
		if ( is_object( $value) && !is_a( $value, 'Zef\Zel\IValueAdapter')) {
			$rsv = new ObjectResolver( $value);
			return $rsv;
		}
		if ( is_array( $value)) {
			$arr	=	[];
			foreach ( $value as $item) {
				$arr[]	=	$this->_fixValue( $item);
			}
			return $arr;
		}
		
		return $value;
	}
	
	public static function cleanValue( $value)
	{
	    if ( is_array( $value)) {
	        foreach ( $value as $k=>$v) {
	            if ( $v instanceof IValueAdapter) {
	                $value[$k] = $v->get();
	            }
	        }
	    }
	    
	    return $value;
	}
	
	/**
	 * Returns true if array is indexed (starting with index 0). Will return true for empty arrays too.
	 * @param array $arr
	 * @return boolean
	 */
	public static function isArrayIndexed( $arr)
	{
	    if ( empty( $arr)) {
	        return true;
	    }
	    $keys =   array_keys( $arr);
	    foreach ( $keys as $key) {
	        if ( $key === 0) {
	            return true;
	        }
	        return false;
	    }
	}
	
	// UTIL
	public function __toString()
	{
		return get_class( $this).'';
	}
}