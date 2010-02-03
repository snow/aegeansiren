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

    protected function getSuperClass();

    protected function isSupperAttr($attribute)
    {
        return 'id' != $attribute
            && (in_array($attribute,$this->superInst->attributeNames)
                || in_array($attribute,$this->_superAttrs));
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
        if (null === $this->_superInst)
        {
            $superClass = $this->superClass;
            $this->_superInst = $this->isNewRecord?new $superClass()
                :CActiveRecord::model($superClass)->findByAttributes();
            if (null === $this->_superInst)
            {
                throw new CException('Cant find baseuser for '.get_class($this).'#'.$this->id);
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