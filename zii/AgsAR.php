<?php
/**
 * AegeanSiren ActiveRecord
 * A patch of CActiveRecord
 * @author snow
 *
 */
abstract class AgsAR extends CActiveRecord
{
	protected $_pkList;

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
		$value = Y::t(get_class($this).':'.$attribute.':default');

		if (get_class($this).':'.$attribute.':default' === $value)
		{
			$value = Y::t(get_class($this).':'.$attribute);

			if (get_class($this).':'.$attribute === $value)
			{
				$value = '';
			}
		}

		if ('none' === $value)
		{
			$value = '';
		}
		return $value;
	}

	public function getAttributeLabel($attribute)
	{
		$value = Y::t(get_class($this).':'.$attribute.':label');

		if (get_class($this).':'.$attribute.':label' == $value)
		{
			$value = Y::t(get_class($this).':'.$attribute);
		}

		if ('none' === $value)
		{
			$value = '';
		}
		return $value;
	}

	protected function beforeSave()
	{
		$time = time();
		if ( $this->hasAttribute('created') && $this->isNewRecord)
            $this->created = $time;

        if ( $this->hasAttribute('updated') )
            $this->updated = $time;

        return parent::beforeSave();
	}

	public function setAttributes($values,$safeOnly=true)
	{
		foreach ($values as $k => $v)
		{
			if ($v === $this->getAttributeDefault($k))
			{
				unset($values[$k]);
			}
		}
		parent::setAttributes($values,$safeOnly);
		return $values;
	}

	public function searchWithPKQuery($query,$params)
	{
		return $this->searchWithPKList(Yii::app()->db->createCommand($query)->queryColumn($params));
	}

	public function searchWithPKList($pkList)
	{
		if (null === $this->_pkList)
		{
			$this->_pkList = array();
		}

		foreach ($pkList as $pk)
		{
			$pk = (int)$pk;
			$this->_pkList[$pk] = $pk;
		}

		return $this;
	}

	public function findAll($condition='',$params=array())
	{
		$this->applyPKConditon();
		return parent::findAll($condition,$params);
	}

	public function count($condition='',$params=array())
	{
		$this->applyPKConditon();
		return parent::count($condition,$params);
	}

	public function getDbCriteria($createIfNull=true)
	{
		$this->applyPKConditon();
		return parent::getDbCriteria($createIfNull);
	}

	protected function applyPKConditon()
	{
		/**
		 * null means not any pk condition was added
		 */
		if (null !== $this->_pkList)
		{
			/**
			 * must use array_values() here
			 * curz createInCondition() only recognize 0,1,2... array
			 *
			 * and call getDbCriteria() from parent
			 * to avoid dead loop
			 */
			parent::getDbCriteria()->mergeWith(array(
				'condition'=>count($this->_pkList)?'(`t`.`id` in ('.implode(',',$this->_pkList).'))':'(0=1)',
			));
			$this->_pkList = null;
		}
	}
}