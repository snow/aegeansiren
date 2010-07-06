<?php

class AgsPHPInternalEncoding extends CComponent
{
	public $encode = 'UTF-8';

	public function init()
	{
		if (function_exists('mb_internal_encoding'))
		{
			mb_internal_encoding($this->encode);
		}
		else
		{
			throw new CException();
		}
	}
}