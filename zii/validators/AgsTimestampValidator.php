<?php

class AgsTimestampValidator extends CValidator
{
	public $allowZero = true;
	public $yearRange = false;
	/**
	 * @param $object
	 * @param $attribute
	 */
	protected function validateAttribute($object, $attribute)
	{
		if (0 === $object->$attribute)
		{
			if (!$this->allowZero)
			{
				$object->addError($attribute,'Zero is not allowed for '.$attribute);
			}
		}
		else
		{
			if(is_array($this->yearRange))
			{
				if (is_integer($this->yearRange[0])
					&& is_integer($this->yearRange[1])
					&& $this->yearRange[0] >= 1
					&& $this->yearRange[1] <= 32767)
				{
					$year = strftime('%Y',$object->$attribute);

					if ($year < $this->yearRange[0] || $year > $this->yearRange[1])
					{
						$object->addError($attribute,'Year must be in range('.$this->yearRange[0].','.$this->yearRange[1].').');
					}
				}
				else
				{
					throw new CException('Incorrect config of yearRange('.$this->yearRange[0].','.$this->yearRange[1].')');
				}
			}

			if (!is_numeric($object->$attribute))
			{
				$object->$attribute = strtotime($object->$attribute);
			}

			if (!self::validateTimestamp($object->$attribute))
			{
				$object->addError($attribute,'Invalid timestamp for '.$attribute);
			}
		}
	}

	public static function validateTimestamp($timestamp)
	{
		return checkdate(strftime('%m',$timestamp),strftime('%d',$timestamp),strftime('%Y',$timestamp));
	}
}