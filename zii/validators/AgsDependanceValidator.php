<?php

class AgsDependanceValidator extends CValidator
{
    public $class = null;
    public $allowZero = false;
    /**
     * @param $object
     * @param $attribute
     */
    protected function validateAttribute($object, $attribute)
    {
        if (null === $this->class)
        {
            throw new CException('Must assign depentance class for '.__CLASS__);
        }

        if (0 == $object->$attribute)
        {
            if (!$this->allowZero)
            {
                $object->addError($attribute,'Zero is not allowed for '.$attribute);
            }
        }
        elseif ($this->class === get_class($object) && $object->id === $object->$attribute)
        {
            $object->addError($attribute,$attribute.' could not be same as self');
        }
        elseif (!CActiveRecord::model($this->class)->exists('id=:id',array(':id'=>$object->$attribute)))
        {
            $object->addError($attribute,'Given '.$attribute.': '.$object->$attribute.' doesnt exist.');
        }
    }
}