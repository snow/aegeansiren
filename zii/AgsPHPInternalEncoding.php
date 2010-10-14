<?php
/**
 * preload this component to set application-wide mb_internal_encoding
 * preload这个组件来设置整个应用的所有代码使用的mb_internal_encoding
 *
 * @author snow@firebloom.cc
 *
 */
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