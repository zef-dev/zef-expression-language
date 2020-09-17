<?php declare(strict_types=1);

namespace Zef\Zel;

interface IValueAdapter
{
	public function keys();
	
	public function get();
	
}