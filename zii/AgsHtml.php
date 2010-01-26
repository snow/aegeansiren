<?php

class AgsHtml extends CHtml
{
	public static function hideOnEmpty($attribute)
	{
		return empty($attribute)?'hide':'';
	}

	public static function activeTextField($model,$attribute,$htmlOptions=array())
	{
		if (!isset($htmlOptions['value']))
		{
			$attrWithoutIndex = self::stripBatchIndex($attribute);
			$htmlOptions['value'] = $model->$attrWithoutIndex?
				$model->$attrWithoutIndex:
				$model->getAttributeDefault($attrWithoutIndex);

			$htmlOptions['default'] = $model->getAttributeDefault($attrWithoutIndex);
		}
		return parent::activeTextField($model,$attribute,$htmlOptions);
	}

	public static function activeTextArea($model,$attribute,$htmlOptions=array())
	{
		$attrWithoutIndex = self::stripBatchIndex($attribute);
		if (isset($htmlOptions['value']))
		{
			$model->$attrWithoutIndex = $htmlOptions['value'];
			unset($htmlOptions['value']);
		}
		elseif (!$model->$attrWithoutIndex)
		{
			$model->$attrWithoutIndex = $model->getAttributeDefault($attrWithoutIndex);
		}
		$htmlOptions['default'] = $model->getAttributeDefault($attrWithoutIndex);

		return parent::activeTextArea($model,$attribute,$htmlOptions);
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

	public static function activeFormItem($attribute,$params = array(),$htmlOptions = array())
	{
		if (is_array($attribute)
			&& (is_object($_model = $attribute[0]) || is_string($_model))
			&& ($_attribute = $attribute[1]))
		{
			$_label = isset($params['label'])?(bool)$params['label']:true;
			$_item = '';
			$_inputOpt = isset($htmlOptions['input'])?$htmlOptions['input']:array();
			$_attrWithoutIndex = self::stripBatchIndex($_attribute);

			if ($_label)
			{
				$_labelOpt = isset($htmlOptions['label'])?$htmlOptions['label']:array();
				$_labelOpt['class'] = isset($_labelOpt['class'])?$_labelOpt['class'].' colL':'colL';

				if (is_string($_model))
				{
					if(isset($_labelOpt['for']))
					{
						$for=$_labelOpt['for'];
						unset($_labelOpt['for']);
					}
					else
					{
						$for = $_attribute;
					}

					if(isset($_labelOpt['label']))
					{
						$_labelText=$_labelOpt['label'];
						unset($_labelOpt['label']);
					}
					else
						$_labelText=Y::t(('' === $_model)?$_attribute:$_model.':'.$_attribute);

					$_item .= self::label($_labelText,$for,$_labelOpt);
				}
				else
				{
					$_item .= self::activeLabelEx($_model,$_attribute,$_labelOpt);
				}
			}

			if (isset($_inputOpt['value']))
			{
				//$_defaultValue = $_inputOpt['value'];
			}
			elseif ($_model->$_attribute)
			{
				$_inputOpt['value'] = $_model->$_attribute;
			}
			elseif (isset($params['enableDefaultValue'])?(bool)$params['enableDefaultValue']:false)
			{
				if (is_string($_model))
				{
					$_valueKey = $_attribute;
				}
				else
				{
					$_inputOpt['value'] = $_model->getAttributeDefault(self::stripBatchIndex($attribute));
				}
			}
			else
			{
				$_inputOpt['value'] = '';
			}

			if (is_string($_model))
			{
				if (!isset($_inputOpt['id']))
				{
					$_inputOpt['id'] = $_attribute;
				}
				if (!isset($_inputOpt['name']))
				{
					$_inputOpt['name'] = $_attribute;
				}
			}

			switch ($params['type'])
			{
				default:
				case 'text':
					if (is_string($_model))
					{
						$_inputOpt['type'] = 'text';
						$_input = self::tag('input',$_inputOpt);
					}
					else
					{
						$_input = self::activeTextField($_model,$_attribute,$_inputOpt);
					}
				break;

				case 'password':
					if (is_string($_model))
					{
						$_inputOpt['type'] = 'password';
						$_input = self::tag('input',$_inputOpt);
					}
					else
					{
						$_input = self::activePasswordField($_model,$_attribute,$_inputOpt);
					}
				break;

				case 'textarea':
					if (is_string($_model))
					{
						$_defaultValue = $_inputOpt['value'];
						unset($_inputOpt['value']);
						$_input = AgsHtml::tag('textarea',$_inputOpt,$_defaultValue);
					}
					else
					{
						$_input = self::activeTextArea($_model,$_attribute,$_inputOpt);
					}
				break;

				case 'file':
					if (is_string($_model))
					{
						$_inputOpt['type'] = 'file';
						$_input = self::tag('input',$_inputOpt);
					}
					else
					{
						$_input = self::activeFileField($_model,$_attribute,$_inputOpt);
					}
				break;

				case 'check':
					if (is_string($_model))
					{
						$_inputOpt['type'] = 'checkbox';
						$_input = self::tag('input',$_inputOpt);
					}
					else
					{
						if (true !== $_inputOpt['value'])
						{
							unset($_inputOpt['value']);
						}
						$_input = self::activeCheckBox($_model,$_attribute,$_inputOpt);
					}
				break;

				case 'radioGroup':
				break;

				case 'div':
					unset($_inputOpt['value']);
					unset($_inputOpt['name']);
					$_input = self::tag('div',$_inputOpt,$params['content']);
				break;
			}

			if (isset($params['tip'])?(bool)$params['tip']:false)
			{
				if (is_string($_model))
				{
					$_tipKey = $_attribute;
					if ($_model)
					{
						$_tipKey = $_model.':'.$_tipKey;
					}
				}
				else
				{
					$_tipKey = get_class($_model).':'.$_attribute;
				}
				$_tip = Y::t($_tipKey.':tip');
				if (empty($_tip))
				{
					$_tip = Y::t($_tipKey);
				}
				$_input .= AgsHtml::tag('div',array('class'=>'tip'),$_tip);
			}

			if ('check' === $params['type'])
			{
				$_item = $_input.$_item;
			}
			else
			{
				$_item .= '<div class="colR">'.$_input.'</div>';
			}

			return $_item;
		}
		else
		{
			throw new CException('array contains model and attribute is required for the 1st param in '.__CLASS__.'::'.__FUNCTION__);
		}
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
		return Y::t('longDate',array(
			'{year}'=>self::yearOfStamp($timestamp),
			'{month}'=>self::monthOfStamp($timestamp),
			'{day}'=>self::dayOfStamp($timestamp),
		));
	}

	public static function shortDate($timestamp)
	{
		return (self::yearOfStamp($timestamp) == self::yearOfStamp(time()))?
			Y::t('shortDate',array(
				'{month}'=>self::monthOfStamp($timestamp),
				'{day}'=>self::dayOfStamp($timestamp),
			)):
			self::longDate($timestamp);
	}

	public static function shortDateWitHHMM($timestamp)
	{
		return self::shortDate($timestamp).' '.strftime('%R',$timestamp);
	}

	public static function likeLink($class,$id)
	{
		return parent::link(Y::t($class.':like'),array($class.'/like','id'=>$id),
			array('title'=>Y::t($class.':like'),'class'=>'like s-nti '.$class));
	}

	public static function unlikeLink($class,$id,$params=array())
	{
		return parent::link(Y::t($class.':unlike'),array($class.'/unlike','id'=>$id),
			array('title'=>Y::t($class.':unlike'),'class'=>'like ing s-nti '.$class.($params['rm']?' rm':'')));
	}

	public static function errorSummary($model,$htmlOptions=array(),$header='',$footer='')
	{
		$content='';
		if(!is_array($model))
			$model=array($model);
		foreach($model as $m)
		{
			foreach($m->getErrors() as $errors)
			{
				foreach($errors as $error)
				{
					if($error!='')
						$content.='<div class="li">'.$error.'</li>\n';
				}
			}
		}
		if(!isset($htmlOptions['class']))
			$htmlOptions['class']=self::$errorSummaryCss.' ls';
		return self::tag('div',$htmlOptions,$header.$content.$footer);
	}

	public static function clearer()
	{
		return '<div class="c"></div>';
	}

	protected static function stripBatchIndex($attribute)
	{
		return preg_replace('/\[\d+\]/','',$attribute);
	}
}