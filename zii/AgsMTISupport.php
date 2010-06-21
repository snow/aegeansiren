<?php
/*
 * suport class for multi-table inheritance
 *
 * @author snow
 */
abstract class AgsMTISupport extends AgsAR
{
	protected $_superInst;
	protected $_superAttrs = array();
	protected $_specialAttrs = array('superInst','superClass','isNewRecord','id');

	abstract protected function getSuperClass();

	protected function isSupperAttr($attribute)
	{
		return in_array($attribute,$this->_superAttrs)
				|| in_array($attribute,$this->superInst->attributeNames());
	}

	public function __get($name)
	{
		if (!in_array($name,$this->_specialAttrs) && $this->isSupperAttr($name))
		{
			return $this->superInst->$name;
		}
		else
		{
			return parent::__get($name);
		}
	}

	public function __set($name,$value)
	{
		if (!in_array($name,$this->_specialAttrs) && $this->isSupperAttr($name))
		{
			return $this->superInst->$name = $value;
		}
		else
		{
			return parent::__set($name,$value);
		}
	}

	public function setScenario($value)
	{
		parent::setScenario($value);

		$this->superInst->scenario = $value;
	}

	public function isAttributeRequired($attribute)
	{
		if ($this->isSupperAttr($attribute))
		{
			return $this->superInst->isAttributeRequired($attribute);
		}
		else
		{
			return parent::isAttributeRequired($attribute);
		}
	}

	public function getSuperInst()
	{
		if (null === $this->_superInst)
		{
			$superClass = $this->superClass;

			$this->_superInst = $this->id?
				CActiveRecord::model($superClass)->findByAttributes(array('id'=>$this->id))
				:new $superClass();
			if (null === $this->_superInst)
			{
				throw new CException('Cant find base for '.get_class($this).'#'.$this->id);
			}
		}
		return $this->_superInst;
	}

	public function setAttributes($attributes,$safeOnly=true)
	{
		$attributes = parent::setAttributes($attributes,$safeOnly);

		$this->superInst->attributes = $attributes;

		try {
			$this->superInst->subtype = strtolower(get_class($this));
		}
		catch (Exception $e)
		{
			//nothing,just to avoid
		}
	}

	public function validate($attributes=null)
	{
		if (parent::validate($attributes))
		{
			if ($this->superInst->validate($attributes))
			{
				return true;
			}
			else
			{
				$this->addErrors($this->superInst->getErrors());
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	public function save($runValidation=true,$attributes=null)
	{
		$valid = $runValidation?$this->validate($attributes):true;

		if ($valid)
		{
			if ($this->superInst->save(false,$attributes))
			{
				if ($this->isNewRecord)
				{
					$this->id = $this->superInst->id;
				}

				return parent::save(false,$attributes);
			}
		}

		return false;
	}

	public function delete()
	{
		parent::delete();

		$this->superInst->delete();
	}
}