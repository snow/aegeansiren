<?php
/*
 * suport class for multi-table inheritance
 *
 * @author snow
 */
abstract class AgsMTISupport extends AgsAR
{
	private $_superModel;
	private $_superAttrs;
	private $_superInst;
	protected static $_notSuperAttrs = array('isNewRecord','id');

	abstract protected function getSuperClass();

	protected function getSuperModel()
	{
		if (null === $this->_superModel)
		{
			$this->_superModel = CActiveRecord::model($this->superClass);
		}
		return $this->_superModel;
	}

	protected function getSuperAttrs()
	{
		if (null === $this->_superAttrs)
		{
			$this->_superAttrs = array_merge(
				$this->superModel->attributeNames(),
				array_keys($this->superModel->relations())
			);
		}
		return $this->_superAttrs;
	}

	public function getSuperInst()
	{
		if (!($this->_superInst instanceof $this->superClass) || (('insert' !== $this->scenario) && ($this->id !== $this->_superInst->id)))
		{
			$this->_superInst = $this->id?
				CActiveRecord::model($this->superClass)->findByPk($this->id)
				:new $this->superClass;
			if (null === $this->_superInst)
			{
				throw new CException('Cant find base for '.get_class($this).'#'.$this->id);
			}
		}
		return $this->_superInst;
	}

	protected function isSupperAttr($attribute)
	{
		return (!in_array($attribute,self::$_notSuperAttrs))
			&& (in_array($attribute,$this->superAttrs)
				|| method_exists($this->superModel,'get'.ucfirst($attribute)));
	}

	public function __get($name)
	{
		$getter = 'get'.$name;

		if (method_exists($this,$getter))
		{
			return $this->$getter();
		}
		elseif ($this->isSupperAttr($name))
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
		$setter = 'set'.$name;

		if (method_exists($this,$setter))
		{
			return $this->$setter($value);
		}
		elseif ($this->isSupperAttr($name))
		{
			return $this->superInst->$name = $value;
		}
		else
		{
			return parent::__set($name,$value);
		}
	}

	public function __call($name,$args)
	{
		if (method_exists($this->superInst,$name))
		{
			call_user_func_array(array($this->superInst,$name),$args);
		}
	}

	public function setScenario($value)
	{
		parent::setScenario($value);

		if ($this->superInst instanceof CActiveRecord)
		{
			$this->superInst->scenario = $value;
		}
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

	public function setAttributes($attributes,$safeOnly=true)
	{
		parent::setAttributes($attributes,$safeOnly);

		$this->superInst->attributes = $attributes;

		try {
			$this->superInst->subtype = strtolower(get_class($this));
		}
		catch (Exception $e)
		{
			//nothing,just to avoid exception being throw
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