<?php

class AgsIpAddressValidator extends CValidator
{
	public $allowEmpty = true;
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
			if (!S::isIpAddress($object->$attribute))
			{
				$object->addError($attribute,'Invalid qq for '.$attribute);
			}
		}
	}
}