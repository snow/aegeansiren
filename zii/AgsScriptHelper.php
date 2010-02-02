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
		$temp = explode('}',$cssContent);
		$rules = array();
		foreach ($temp as $e)
		{
			$e = explode('{',$e);
			/* the selector */
			$e[0] = preg_replace('/[\s\n\r]+/',' ',$e[0]);
			$e[0] = preg_replace('/,\s+/',',',$e[0]);
			$e[0] = trim($e[0]);
			/* attributes */
			$e[1] = explode(';',$e[1]);
			foreach ($e[1] as $attr)
			{
				if ('' !== trim($attr))
				{
					$temp = explode(':',$attr);
					$temp[0] = preg_replace('/[\s\n\r]+/','',$temp[0]);
					/* attr name */
					if ('' !== $temp[0])
					{
						$temp[1] = preg_replace('/[\s\n\r]+/',' ',$temp[1]);
						$temp[1] = trim($temp[1]);
						/* attr value */
						if ('' !== $temp[1])
						{
							$rules[$e[0]][$temp[0]] = $temp[1];
						}
					}
				}
			}

		}
		$cssContent = '@CHARSET "UTF-8";'.(YII_DEBUG?chr(10):'');
		foreach ($rules as $selector=>$attributes)
		{
			$attrStr = array();
			foreach ($attributes as $attr=>$value)
			{
				$attrStr[] = $attr.':'.$value;
			}
			$cssContent .= $selector.'{'.implode(';',$attrStr).'}'.(YII_DEBUG?chr(10):'');
		}
		file_put_contents($this->_cssPath.'/'.$this->_cssClientFiles[$clientFile],$cssContent);
	}

	public function cleanCssFile($clientFile)
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
}