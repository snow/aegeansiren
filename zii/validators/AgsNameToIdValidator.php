<?php

class AgsNameToIdValidator extends CValidator
{
	public $class = null;
	public $allowEmpty = true;
	/**
	 * @param $object
	 * @param $attribute
	 */
	protected function validateAttribute($object, $attribute)
	{
		if (null === $this->class)
		{
			throw new CException('Must assign class for '.__CLASS__.' to convert');
		}

		$attrName = $attribute;
		$attrId = substr($attribute,0,-4).'Id';

		if (is_array($object->$attribute))
		{
			$attrNames = AgsSeraph::getSingleForm($attribute).'Names';
			if (isset($object->$attrNames))
			{
				$object->$attrNames = $object->$attribute;
			}
			$resultAr = array();
			foreach ($object->$attribute as $name)
			{
				$result = self::findObjectByName($this->class,$name);
				if ($result instanceof $this->class)
				{
					$resultAr[] = $result->id;
				}
				else
				{
					$object->addError($attrName,Y::t('error:notFound',array('{entity}'=>$object->$attrName)));
					if (is_array($result) && sizeof($result))
					{
						$suggestions = array();
						foreach ($result as $r)
						{
							$suggestions[] = $r;
						}
						$object->addError($attrName,Y::t('searchSuggestion').implode(',',$suggestions));
					}
				}
			}
			$object->$attribute = $resultAr;
		}
		else
		{
			if (! (isset($object,$attrName) && isset($object,$attrId)) )
			{
				throw new CException(get_class($object).' doesnt have a name-id attribute pair to convert.');
			}

			if (empty($object->$attrName))
			{
				if ($this->allowEmpty)
				{
					$object->$attrId = 0;
				}
				else
				{
					$object->addError($attrName,$attrName.' could not be empty');
				}
			}
			else
			{
				$result = self::findObjectByName($this->class,$object->$attrName);
				if ($result instanceof $this->class)
				{
					$object->$attrId = $result->id;
				}
				else
				{
					$object->addError($attrName,Y::t('error:notFound',array('{entity}'=>$object->$attrName)));
					if (is_array($result) && sizeof($result))
					{
						$suggestions = array();
						foreach ($result as $r)
						{
							$suggestions[] = $r;
						}
						$object->addError($attrName,Y::t('searchSuggestion').implode(',',$suggestions));
					}
				}
			}
		}
	}

	public static function findObjectByName($class,$name)
	{
		$object = CActiveRecord::model($class)->findByAttributes(array('name'=>$name));

		if ($object instanceof $class)
		{
			return $object;
		}
		else
		{
			$query = 'select `name` from `'
				.CActiveRecord::model($class)->tableName()
				.'` where name like \'%'.$name.'%\'';
			return Yii::app()->db->createCommand($query)->queryColumn();
		}
	}
}