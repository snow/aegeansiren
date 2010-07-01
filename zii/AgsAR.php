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

	public function getAttributeLabel($attribute)
	{
		$messageKey = get_class($this).':'.$attribute.':label';
		$label = Y::t('local',$messageKey);

		if ($messageKey == $label)
		{
			$label = Y::t('local',get_class($this).':'.$attribute);
		}

		if ('none' === $label)
		{
			$label = '';
		}
		return $label;
	}

	protected function beforeValidate()
	{
		if (in_array($this->scenario,array('insert','update')))
		{
			$time = time();
			if ( $this->hasAttribute('created') && $this->isNewRecord)
				$this->created = $time;

			if ( $this->hasAttribute('updated') )
				$this->updated = $time;
		}

		return parent::beforeValidate();
	}

	public function searchWithPKQuery($query,$params,$or=false)
	{
		return $this->searchWithPKList(Yii::app()->db->createCommand($query)->queryColumn($params),$or);
	}

	public function searchWithPKList($pkList,$or=false)
	{
		if ($or)
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
		}
		else
		{
			if (null !== $this->_pkList)
			{
				foreach ($pkList as $k=>$v)
				{
					if (!in_array($v,$this->_pkList))
					{
						unset($pkList[$k]);
					}
				}
			}
			$this->_pkList = $pkList;
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