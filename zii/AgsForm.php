<?php

class AgsForm extends CFormModel
{
	public function __get($name)
	{
		$getter = 'get'.$name;

		if (method_exists($this,$getter))
		{
			return $this->$getter();
		}
		else
		{
			return parent::__get($name);
		}
	}

	public function __set($name,$value)
	{
		$setter = 'set'.$name;

		if (method_exists($this,$setter))
		{
			return $this->$setter($value);
		}
		else
		{
			return parent::__set($name,$value);
		}
	}

	public function getAttributeDefault($attribute)
	{
		$value = Y::t('ags',get_class($this).':'.$attribute.':default');

		if (get_class($this).':'.$attribute.':default' == $value)
		{
			$value = Y::t('ags',get_class($this).':'.$attribute);
		}

		if ('none' === $value)
		{
			$value = '';
		}
		return $value;
	}

	public function getAttributeLabel($attribute)
	{
		$value = Y::t('ags',get_class($this).':'.$attribute.':label');

		if (get_class($this).':'.$attribute.':label' == $value)
		{
			$value = Y::t('ags',get_class($this).':'.$attribute);
		}

		if ('none' === $value)
		{
			$value = '';
		}
		return $value;
	}
}