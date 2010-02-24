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
			if (!self::validateQq($object->$attribute))
			{
				$object->addError($attribute,'Invalid qq for '.$attribute);
			}
		}
	}

	public static function validateQq($qq)
	{
		return is_numeric($qq)?(($len = strlen($qq)) >= 5 && $len <= 10):preg_match(self::emailPattern,$qq);
	}
}