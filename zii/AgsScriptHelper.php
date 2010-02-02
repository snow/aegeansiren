<?php

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
		 */
		if (!isset($this->_cssClientFiles[$clientFile]))
		{
			if (in_array($this->_browser->browser,$this->cssMap[$clientFile]['specialBrowsers']))
			{
				$basefilename .= '-'.$this->_browser->browser;
			}
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
			else
			{
				$basefilename .= '-'.'default';
			}

			$lastM = 0;
			$mtimes = array();
			foreach ($this->cssMap[$clientFile]['files'] as $cssFile)
			{
				$mtimes[] = filemtime($this->_cssPath.'/'.$cssFile);
			}
			$lastM = max($mtimes);
			$filename = $basefilename.'-'.$lastM.'.css';
			$this->_cssClientFiles[$clientFile] = $filename;
		}

		if (!file_exists($this->_cssPath.'/'.$this->_cssClientFiles[$clientFile]))
		{
			$this->cleanCssFile($basefilename);
			$this->genCssFile($clientFile);
		}
		return $this->_cssClientFiles[$clientFile];
	}

	protected function genCssFile($clientFile)
	{
		foreach ($this->cssMap[$clientFile]['files'] as $cssFile)
		{
			$cssContent .= file_get_contents($this->_cssPath.'/'.$cssFile);
		}
		$cssContent = preg_replace('/\/\*+.+\*+\/|\/\*+\s*\n|\*+\s+.*\n|\*+\/\s*\n/','',$cssContent);
		$cssContent = preg_replace('/@CHARSET.+\n/i','',$cssContent);
		$rulesTmp = explode('}',$cssContent);
		$rules = array();
		foreach ($rulesTmp as $ruleTmp)
		{
			list($selector,$propertyTmp) = explode('{',$ruleTmp);

			if ('' === ($selector = $this->browserCssHackFilter(
				trim(
					preg_replace('/,\s+/',',',
						preg_replace('/[\s\n\r]+/',' ',$selector)
					)
				)
			))) continue;

			$propertities = explode(';',$propertyTmp);
			foreach ($propertities as $propertyStr)
			{
				list($property,$value) = explode(':',$propertyStr);

				if ('' === ($property = $this->browserCssHackFilter(
					preg_replace('/[\s\n\r]+/','',$property),true)
				)) continue;

				if (('' !== ($value = trim(preg_replace('/[\s\n\r]+/',' ',$value))))
					&&(!isset($rules[$selector][$property])
						|| (false === strpos($rules[$selector][$property],'!important'))))
				{
					$rules[$selector][$property] = $value;
				}
			}
		}
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
				if (0 === strpos($file,$clientFile))
				{
					unlink($this->_cssPath.'/'.$file);
				}
			}
		}
	}

	protected function browserCssHackFilter($rule,$isProperty=false)
	{
		if ($isProperty)
		{
			/* IE 6 and below */
			if ((0 === strpos($rule,'* html'))
				&& (('IE' !== $this->_browser->browser) || (6 < (int)$this->_browser->majorver)))
			{
				return '';
			}
		}
		else
		{
			/* IE 6 and below */
			if ((0 === strpos($rule,'* html'))
				&& (('IE' !== $this->_browser->browser) || (6 < (int)$this->_browser->majorver)))
			{
				return '';
			}
			/* IE 7 only */
			if ((0 === strpos($rule,'*:first-child+html'))
				&& (('IE' !== $this->_browser->browser) || (7 !== (int)$this->_browser->majorver)))
			{
				return '';
			}
			/* IE 7 and modern browsers only */
			if ((0 === strpos($rule,'html>body'))
				&& (('IE' === $this->_browser->browser) && (7 > (int)$this->_browser->majorver)))
			{
				return '';
			}
			/* Modern browsers only (not IE 7) */
			if ((0 === strpos($rule,'html>/**/body'))
				&& (('IE' === $this->_browser->browser) && (8 > (int)$this->_browser->majorver)))
			{
				return '';
			}
			/* Recent Opera versions 9 and below */
			if ((0 === strpos($rule,'html:first-child'))
				&& (('Opera' !== $this->_browser->browser) || (9 < (int)$this->_browser->majorver)))
			{
				return '';
			}
		}
		return $rule;
	}
}