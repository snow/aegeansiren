<?php
/**
 * AegeanSiren ActiveRecord
 * enhancements of CFormModel
 * CFormModel的一系列增强
 *
 * Chg: prefer to getter or setter method than to access property directly in __get() and __set()
 * Chg: use i18n system to provide attribute label and default value instead of hard-coded in method
 *
 * getter和setter方法在__get和__set里有更高的优先级
 * 用i18n系统来提供属性label和默认值，取代原来硬编码成一个类方法
 *
 * @author snow@firebloom.cc
 *
 */
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

	/**
	 * override to provide attr default value by language system
	 * 从语言文件获得属性默认值
	 *
	 * @param string $attribute
	 */
	public function getAttributeDefault($attribute)
	{
		$value = Y::t('local',get_class($this).':'.$attribute.':default');

		if (get_class($this).':'.$attribute.':default' == $value)
		{
			$value = Y::t('local',get_class($this).':'.$attribute);
		}

		if ('none' === $value)
		{
			$value = '';
		}
		return $value;
	}

	/**
	 * override to provide attr label by language system
	 * 从语言文件获得属性label
	 *
	 * @param string $attribute
	 */
	public function getAttributeLabel($attribute)
	{
		$label = Y::t('local',get_class($this).':'.$attribute.':label');

		if (get_class($this).':'.$attribute.':label' == $label)
		{
			$label = Y::t('local',get_class($this).':'.$attribute);
		}

		if ('none' === $label)
		{
			$label = '';
		}
		return $label;
	}
}