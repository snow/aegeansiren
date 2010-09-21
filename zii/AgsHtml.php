<?php

class AgsHtml extends CHtml
{
	public static function hideOnEmpty($attribute)
	{
		return empty($attribute)?'s-h':'';
	}

	public static function activeTextField($model,$attribute,$htmlOptions=array())
	{
		if (!isset($htmlOptions['value']) && $htmlOptions['default'])
		{
			$attrWithoutIndex = self::stripBatchIndex($attribute);
			$htmlOptions['value'] = $model->$attrWithoutIndex?
				$model->$attrWithoutIndex:
				$model->getAttributeDefault($attrWithoutIndex);

			$htmlOptions['default'] = $model->getAttributeDefault($attrWithoutIndex);
		}
		$htmlOptions['class'] .= ' text';
		return parent::activeTextField($model,$attribute,$htmlOptions);
	}

	public static function activeTextArea($model,$attribute,$htmlOptions=array())
	{
		if ($htmlOptions['default'])
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
		}

		return parent::activeTextArea($model,$attribute,$htmlOptions);
	}

	public static function activePasswordField($model,$attribute,$htmlOptions=array())
	{
		$htmlOptions['class'] .= ' password';
		return parent::activePasswordField($model,$attribute,$htmlOptions);
	}

	public static function activeStampField($model,$attribute,$htmlOptions=array())
	{
		$attrWithoutIndex = self::stripBatchIndex($attribute);
		if (!isset($htmlOptions['value']))
			$htmlOptions['value'] = $model->$attrWithoutIndex?
				strftime('%Y-%m-%d',$model->$attrWithoutIndex):
				($htmlOptions['default']=$model->getAttributeDefault($attrWithoutIndex));

		$htmlOptions['class'] .= ' text';
		return parent::activeTextField($model,$attribute,$htmlOptions);
	}

	public static function longDate($timestamp)
	{
		return Y::t('ags','longDate',array(
			'{year}'=>date('Y',$timestamp),
			'{month}'=>date('m',$timestamp),
			'{day}'=>date('d',$timestamp),
		));
	}

	public static function shortDate($timestamp)
	{
		return (date('Y',$timestamp) === date('Y'))?
			Y::t('ags','shortDate',array(
				'{month}'=>date('m',$timestamp),
				'{day}'=>date('d',$timestamp),
			)):
			self::longDate($timestamp);
	}

	public static function friendlyTime($timestamp)
	{
		$diff = time() - (int)$timestamp;

		$minute = 60;
		$hour = $minute * 60;
		$day = $hour * 24;
		$month = $day * 30;

		if ($diff < $minute)
		{
			return Y::t('ags','friendlytime:justnow');
		}
		else if ($diff < $hour)
		{
			$diff = round($diff / $minute);
			if ($diff == 0)
			{
				$diff = 1;
			}

			return Y::t('ags','friendlytime:minutes',array('{interval}'=>$diff));
		}
		else if ($diff < $day)
		{
			$diff = round($diff / $hour);
			if ($diff == 0) {
				$diff = 1;
			}

			return Y::t('ags','friendlytime:hours',array('{interval}'=>$diff));
		}
		else if ($diff < $month)
		{
			$diff = round($diff / $day);
			if ($diff == 0) {
				$diff = 1;
			}

			return Y::t('ags','friendlytime:days',array('{interval}'=>$diff));
		}
		else
		{
			$diff = round($diff / $month);
			if ($diff == 0) {
				$diff = 1;
			}

			if ($diff > 5)
			{
				return Y::t('ags','friendlytime:longAgo');
			}
			else
			{
				return Y::t('ags','friendlytime:months',array('{interval}'=>$diff));
			}
		}
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

	public static function qqLink($qq,$htmlOptions=array())
	{
		$htmlOptions['class'] .= 's-chLn s-chLnQ';
		return self::link(self::image('http://wpa.qq.com/pa?p=1:'.$qq.':41','通过QQ与Ta交谈').$qq,
		   'Tencent://Message/?Uin='.$qq.'&websiteName=校园招聘',$htmlOptions);
	}

	public static function gtalkLink($gtalk,$htmlOptions=array())
	{
		$htmlOptions['class'] .= 's-chLn s-chLnGTalk';
		return self::link($gtalk,'gtalk:chat?jid='.$gtalk,$htmlOptions);
	}

	public static function msnLink($msn,$htmlOptions=array())
	{
		$htmlOptions['class'] .= 's-chLn s-chLnMsn';
		return self::link($msn,'msnim:chat?contact='.$msn,$htmlOptions);
	}

	public static function getUrlFavicon($url)
	{
		return 'http://google.com/s2/favicons?domain='.S::getUrlDomain($url);
	}

	protected static function stripBatchIndex($attribute)
	{
		return preg_replace('/\[\d+\]/','',$attribute);
	}
}