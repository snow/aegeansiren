<?php

class AgsHtml extends CHtml
{
	public static function hideOnEmpty($attribute)
	{
		return empty($attribute)?'hide':'';
	}

	public static function activeStampField($model,$attribute,$htmlOptions=array())
	{
		$attrWithoutIndex = self::stripBatchIndex($attribute);
		if (!isset($htmlOptions['value']))
			$htmlOptions['value'] = $model->$attrWithoutIndex?
				strftime('%Y-%m-%d',$model->$attrWithoutIndex):
				$model->getAttributeDefault($attrWithoutIndex);
		return parent::activeTextField($model,$attribute,$htmlOptions);
	}

	public static function yearOfStamp($timestamp)
	{
		return strftime('%Y',$timestamp);
	}

	public static function monthOfStamp($timestamp)
	{
		return preg_replace('/^0/','',strftime('%m',$timestamp));
	}

	public static function dayOfStamp($timestamp)
	{
		return strftime('%e',$timestamp);
	}

	public static function longDate($timestamp)
	{
		return Y::t('ags','longDate',array(
			'{year}'=>self::yearOfStamp($timestamp),
			'{month}'=>self::monthOfStamp($timestamp),
			'{day}'=>self::dayOfStamp($timestamp),
		));
	}

	public static function shortDate($timestamp)
	{
		return (self::yearOfStamp($timestamp) == self::yearOfStamp(time()))?
			Y::t('ags','shortDate',array(
				'{month}'=>self::monthOfStamp($timestamp),
				'{day}'=>self::dayOfStamp($timestamp),
			)):
			self::longDate($timestamp);
	}

	public static function shortDateWitHHMM($timestamp)
	{
		return self::shortDate($timestamp).' '.strftime('%R',$timestamp);
	}

	public static function errorSummary($model,$htmlOptions=array(),$header='',$footer='')
	{
		$content='';
		if(!is_array($model))
			$model=array($model);
		foreach($model as $m)
		{
			if ($m instanceof CModel)
			{
				foreach($m->getErrors() as $errors)
				{
					if(!is_array($errors))
						$errors=array($errors);
					foreach($errors as $error)
					{
						if($error!='')
							$content.='<div class="li">'.$error.'</div>';
					}
				}
			}
			elseif (is_array($m))
			{
				foreach ($m as $error)
				{
					if($error!='')
							$content.='<div class="li">'.$error.'</div>';
				}
			}
			else
			{
				$content.='<div class="li">'.$m.'</div>';
			}
		}
		if ('' == $content)
		{
			return '';
		}
		if(!isset($htmlOptions['class']))
			$htmlOptions['class']=self::$errorSummaryCss.' ls';
		return self::tag('div',$htmlOptions,$header.$content.$footer);
	}

	public static function clearer()
	{
		return '<div class="s-c"></div>';
	}

	protected static function stripBatchIndex($attribute)
	{
		return preg_replace('/\[\d+\]/','',$attribute);
	}
}