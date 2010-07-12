<?php
/**
 * CStringValidator class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CStringValidator validates that the attribute value is of certain length.
 *
 * Note, this validator should only be used with string-typed attributes.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CStringValidator.php 1354 2009-08-20 18:15:14Z qiang.xue $
 * @package system.validators
 * @since 1.0
 */
class AgsStringValidator extends CValidator
{
	/**
	 * @var integer maximum length. Defaults to null, meaning no maximum limit.
	 */
	public $max;
	/**
	 * @var integer minimum length. Defaults to null, meaning no minimum limit.
	 */
	public $min;
	/**
	 * @var integer exact length. Defaults to null, meaning no exact length limit.
	 */
	public $is;
	/**
	 * @var string user-defined error message used when the value is too long.
	 */
	public $tooShort;
	/**
	 * @var string user-defined error message used when the value is too short.
	 */
	public $tooLong;
	/**
	 *
	 */
	public $onlyUrlCharacters=false;
	/**
	 *
	 */
	public $safeText=false;
	/**
	 * @var boolean whether the attribute value can be null or empty. Defaults to true,
	 * meaning that if the attribute is empty, it is considered valid.
	 */
	public $allowEmpty=true;

	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param CModel the object being validated
	 * @param string the attribute being validated
	 */
	protected function validateAttribute($object,$attribute)
	{
		$value=$object->$attribute;
		if($this->allowEmpty && $this->isEmpty($value))
			return;
		$length=mb_strlen($value,'utf8');
		if($this->min!==null && $length<$this->min)
		{
			$message=$this->tooShort!==null?$this->tooShort:Y::t('ags','err:tooShort');
			$this->addError($object,$attribute,$message,array('{min}'=>$this->min));
		}
		if($this->max!==null && $length>$this->max)
		{
			$message=$this->tooLong!==null?$this->tooLong:Y::t('ags','err:tooLong');
			$this->addError($object,$attribute,$message,array('{max}'=>$this->max));
		}
		if($this->is!==null && $length!==$this->is)
		{
			$message=$this->message!==null?$this->message:Y::t('ags','err:notMatchFixedLength');
			$this->addError($object,$attribute,$message,array('{length}'=>$this->is));
		}
		if ($this->onlyUrlCharacters)
		{
			if (preg_match('/[^abcdefghijklmnopqrstuvwxyz\d_\-\.]/',$object->$attribute))
			{
				$this->addError($object,$attribute,Y::t('ags','err:onlyValidUrlCharactersAllowed'));
			}
		}
		if ($this->safeText)
		{
			if (preg_match('/[^\w\d_\s\-\.]/',$object->$attribute))
			{
				$this->addError($object,$attribute,Y::t('ags','err:unsafeCharacterDetected'));
			}
		}
	}
}

