<?php
/**
 * A helper class that could use for
 * 1.merge css files into one
 * 2.generate different css file for different browser
 *  filter special css rule like * html {} for ie6 to show in only css file for ie6
 * 	and give user correct file depends on user-agent
 *
 * config exzample:
 * 'scriptHelper'=>array(
 *		'class'=>'AgsScriptHelper',
 *		'cssMap'=>array(
 *			'base'=>array(
 *				'files'=>array('main.css'),
 *				'specialBrowsers'=>array('IE'=>array(8,7,6)),
 *			),
 * 		),
 * ),
 *
 * and main.css contains
 * .example {
 *    font-size:10px;
 *    *font-size:7px;
 * }
 * * html .example {
 *    font-size:6px;
 * }
 *
 * then,call Yii::app()->scriptHelper->getCss('base') in header
 * will give IE6 user base-ie-6.css contains
 * .example{font-size:6px}
 * IE7 user base-ie-7.css contains
 * .example{font-size:7px}
 * and FF and chrome .etc users base-default.css contains
 * .example{font-size:10px}
 *
 * @package AgeanSiren
 * @author Snow.Hellsing snow.hellsing@gmail.com
 * @link http://twitter.com/snowhs
 * @license GPLv3
 */
class AgsScriptHelper extends CComponent
{
	public $cssMap;
	public $cssPathAlias = 'webroot.css';
	protected $_browser;
	protected $_cssPath;
	protected $_cssClientFiles = array();

	public function init()
	{
		$this->_browser = get_browser();
		$this->_cssPath = Yii::getPathOfAlias($this->cssPathAlias);
		if (!is_array($this->cssMap))
		{
			throw new CException(Y::t('ags',__CLASS__.'\'s param $cssMap is not configured correctly'));
		}
	}

	public function getCss($clientFile)
	{
		$basefilename = $clientFile;
		/*
		 * compute filename
		 * assign special filename to special browser
		 * so later could generate special css for it
		 */
		if (!isset($this->_cssClientFiles[$clientFile]))
		{
			/*
			 * match something like 'specialBrowsers'=>array('IE','Opera')
			 */
			if (in_array($this->_browser->browser,$this->cssMap[$clientFile]['specialBrowsers']))
			{
				$basefilename .= '-'.$this->_browser->browser;
			}
			/*
			 * like 'specialBrowsers'=>array('IE'=>array(6,7,8))
			 */
			elseif (is_array($browser = $this->cssMap[$clientFile]['specialBrowsers'][$this->_browser->browser]))
			{
				$ver = (int)$this->_browser->majorver;
				if (in_array($ver,$browser))
				{
					$basefilename .= '-'.$this->_browser->browser.'-'.$ver;
				}
				else
				{
					for ($i = $ver;$i>=min($browser);$i--)
					{
						if (in_array($i,$browser))
						{
							$basefilename .= '-'.$this->_browser->browser.'-'.$i;
							break;
						}
					}
					if (null === $this->_cssClientFile)
					{
						for ($i = $ver;$i<=max($browser);$i++)
						{
							if (in_array($i,$browser))
							{
								$basefilename .= '-'.$this->_browser->browser.'-'.$i;
								break;
							}
						}
					}
					if ($basefilename === $clientFile)
					{
						throw new CException(Y::t('ags','Could not detemin finename for {browser}{ver}',array(
							'{browser}'=>$this->_browser->browser,
							'{ver}'=>$ver,
						)));
					}
				}
			}
			/*
			 * thank stars
			 */
			else
			{
				$basefilename .= '-'.'default';
			}

			if (YII_DEBUG)
			{
				/*
				 * in developing mode
				 * detect the last modify time of all to-merge css files
				 * and append it to the generated file
				 * so when we modified the source css files
				 * the geerated file will be updated automaticly
				 */
				$lastM = 0;
				$mtimes = array();
				foreach ($this->cssMap[$clientFile]['files'] as $cssFile)
				{
					$mtimes[] = filemtime($this->_cssPath.'/'.$cssFile);
				}
				$lastM = max($mtimes);
				$filename = $basefilename.'-'.$lastM.'.css';
				/*
				 * the latest css file not generated,let's clean old file(s) and generate the new one
				 */
				if (!file_exists($this->_cssPath.'/'.$this->_cssClientFiles[$clientFile]))
				{
					$this->cleanCssFile($basefilename);
					$this->genCssFile($clientFile);
				}
			}
			else
			{
				/*
				 * in production mode
				 * let's do the update job manually
				 * to save user loading time
				 */
				$filename = $basefilename.'.css';
			}
			$this->_cssClientFiles[$clientFile] = $filename;
		}


		return $this->_cssClientFiles[$clientFile];
	}

	protected function genCssFile($clientFile)
	{
		/* load all files which to merge */
		foreach ($this->cssMap[$clientFile]['files'] as $cssFile)
		{
			$cssContent .= file_get_contents($this->_cssPath.'/'.$cssFile);
		}
		/* strip comments and charset */
		$cssContent = preg_replace('/\/\*+.+\*+\/|\/\*+\s*\n|\*+\s+(?!html).*\n|\*+\/\s*\n/','',$cssContent);
		$cssContent = preg_replace('/@CHARSET.+\n/i','',$cssContent);
		/* split each "SELETCTOR {}" into array items */
		$rulesTmp = explode('}',$cssContent);
		/* this array will store well processed rules */
		$rules = array();
		foreach ($rulesTmp as $ruleTmp)
		{
			list($selector,$propertyTmp) = explode('{',$ruleTmp);
			/* if it is not a selector or not use for current user agent,ignore it */
			if ('' === ($selector = $this->browserCssHackFilter(
				trim(/* strip various format blanks */
					preg_replace('/,\s+/',',',
						preg_replace('/[\s\n\r]+/',' ',$selector)
					)
				)
			))) continue;

			$propertities = explode(';',$propertyTmp);
			foreach ($propertities as $propertyStr)
			{
				list($property,$value) = explode(':',$propertyStr);
				/* if it is not a property or not use for current user agent,ignore it */
				if ('' === ($property = $this->browserCssHackFilter(
					preg_replace('/[\s\n\r]+/','',$property),true)
				)) continue;

				if (('' !== ($value = trim(preg_replace('/[\s\n\r]+/',' ',$value))))
					&&(!isset($rules[$selector][$property])/* check if there is already a !important rule */
						|| (false === strpos($rules[$selector][$property],'!important'))))
				{
					$rules[$selector][$property] = $value;
				}
			}
		}
		/*
		 * now we got a array contains well processed rules
		 * let's generate the css file
		 */
		/* append line break on in developing mode */
		$cssContent = '@charset "utf-8";'.(YII_DEBUG?chr(10):'');
		foreach ($rules as $selector=>$propertities)
		{
			$propertyStr = array();
			foreach ($propertities as $property=>$value)
			{
				$propertyStr[] = $property.':'.$value;
			}
			$cssContent .= $selector.'{'.implode(';',$propertyStr).'}'.(YII_DEBUG?chr(10):'');
		}
		file_put_contents($this->_cssPath.'/'.$this->_cssClientFiles[$clientFile],$cssContent);
	}

	protected function cleanCssFile($clientFile)
	{
		if ($dir = opendir($this->_cssPath))
		{
			while ($file = readdir($dir))
			{
				/* if it starts with the basefilename like "base-default",it's to remove */
				if (0 === strpos($file,$clientFile))
				{
					unlink($this->_cssPath.'/'.$file);
				}
			}
		}
	}

	protected function browserCssHackFilter($rule,$isProperty=false)
	{
		/*
		 * if the selector or property match some special format
		 * it's some browser's hack
		 * if it's right its show time now,return it as a normal rule
		 * else,tell it should be ignore by return ''
		 */
		if ($isProperty)
		{
			/* IE 6 and below */
			if (in_array(substr($rule,0,1),array('_','-')))
			{
				if (('IE' === $this->_browser->browser) && (7 > (int)$this->_browser->majorver))
				{
					return trim(substr($rule,1));
				}
				else
				{
					return '';
				}
			}
			/* IE 7 and below */
			if ('*'===substr($rule,0,1))
			{
				if (('IE' === $this->_browser->browser) && (8 > (int)$this->_browser->majorver))
				{
					return trim(substr($rule,1));
				}
				else
				{
					return '';
				}
			}
		}
		else
		{
			/* IE 6 and below */
			if (0 === strpos($rule,'* html'))
			{
				if (('IE' === $this->_browser->browser) && (7 > (int)$this->_browser->majorver))
				{
					return trim(substr($rule,6));
				}
				else
				{
					return '';
				}
			}
			/* IE 7 only */
			if (0 === strpos($rule,'*:first-child+html'))
			{
				if (('IE' === $this->_browser->browser) && (7 === (int)$this->_browser->majorver))
				{
					return trim(substr($rule,18));
				}
				else
				{
					return '';
				}
			}
			/* IE 7 and modern browsers only */
			if (0 === strpos($rule,'html>body'))
			{
				if (('IE' !== $this->_browser->browser) || (6 < (int)$this->_browser->majorver))
				{
					return trim(substr($rule,10));
				}
				else
				{
					return '';
				}
			}
			if (false !== strpos($rule,'['))
			{
				if (('IE' !== $this->_browser->browser) || (6 < (int)$this->_browser->majorver))
				{
					return trim(substr($rule,10));
				}
				else
				{
					return '';
				}
			}
			/* Modern browsers only (not IE 7) */
			if (0 === strpos($rule,'html>/**/body'))
			{
				if (('IE' !== $this->_browser->browser) || (7 < (int)$this->_browser->majorver))
				{
					return trim(substr($rule,14));
				}
				else
				{
					return '';
				}
			}
			/*
			 * sorry opera.I decide to ignore u although I use opear mini months ago
			 */
			/* Recent Opera versions 9 and below */
			/*if ((0 === strpos($rule,'html:first-child'))
				&& (('Opera' !== $this->_browser->browser) || (9 < (int)$this->_browser->majorver)))
			{
				return '';
			}*/
		}
		/*
		 * normal rule,return it originly
		 */
		return $rule;
	}
}