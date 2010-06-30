<?php
/*
 * suport class for multi-table inheritance
 *
 * @author snow
 */
abstract class AgsMTISupport extends AgsAR
{
	protected $_superInst;
	protected $_superClass;
	protected static $_specialAttrs = array('superInst','superClass','superAttrs','isNewRecord','id');

	abstract protected function getSuperAttrs();

	abstract protected function getSuperClass();

	protected function isSupperAttr($attribute)
	{
		return (!in_array($attribute,self::$_specialAttrs)) &&
			(in_array($attribute,$this->superAttrs)
				|| in_array($attribute,CActiveRecord::model($this->superClass)->attributeNames()));
	}

	public function __get($name)
	{
		if ($this->isSupperAttr($name))
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
		if ($this->isSupperAttr($name))
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
		if (null === $this->_superClass)
		{
			$this->_superClass = $this->getSuperClass();
		}

		if (($this->_superClass !== get_class($this->_superInst)) || (('insert' !== $this->scenario) && ($this->id !== $this->_superInst->id)))
		{
			$this->_superInst = $this->id?
				CActiveRecord::model($this->_superClass)->findByAttributes(array('id'=>(int)$this->id))
				:new $this->_superClass();
			if (null === $this->_superInst)
			{
				throw new CException('Cant find base for '.get_class($this).'#'.$this->id);
			}
		}
		return $this->_superInst;
	}

	public function setAttributes($attributes,$safeOnly=true)
	{
		parent::setAttributes($attributes,$safeOnly);

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