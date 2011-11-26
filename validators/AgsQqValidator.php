<?php

class AgsQqValidator extends CValidator
{
	public $allowEmpty = true;
	const emailPattern = '/^\\w+([-+.]\\w+)*@\\w+([-.]\\w+)*\\.\\w+([-.]\\w+)*$/';
	/**
	 * @param $object
	 * @param $attribute
	 */
	protected function validateAttribute($object, $attribute)
	{
		if (empty($object->$attribute))
		{
			if (!$this->allowEmpty)
			{
				$object->addError($attribute,'Empty is not allowed for '.$attribute);
			}
		}
		else
		{
			if (!S::isTencentQq($object->$attribute))
			{
				$object->addError($attribute,'Invalid qq for '.$attribute);
			}
		}
	}
}