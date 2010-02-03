<?php
/**
 * A helper class that could use for
 * 1.merge css files into one
 * 2.generate different css file for different browser
 *  filter special css rule like * html {} for ie6 to show in only css file for ie6
 *     and give user correct file depends on user-agent
 *
 * config exzample:
 * 'scriptHelper'=>array(
 *        'class'=>'AgsScriptHelper',
 *        'cssMap'=>array(
 *            'base'=>array(
 *                'files'=>array('main.css'),
 *                'specialBrowsers'=>array('trident'=>array(8,7,6)),
 *            ),
 *         ),
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
 * @author Snow.Hellsing <snow.hellsing@gmail.com>
 * @link http://twitter.com/snowhs
 * @license GPLv3
 */
class AgsScriptHelper extends CComponent
{
    public $cssMap;
    public $cssPathAlias = 'webroot.css';
    protected $_browser;
    protected $_browserEngine;
    protected $_browserMVer;
    protected $_cssPath;
    protected $_cssClientFiles = array();
    protected $_cssLastModify;

    public function init()
    {
        $this->_browser = get_browser();
        $this->detectBrowserEngineAndVer();
        $this->_cssPath = Yii::getPathOfAlias($this->cssPathAlias);
        if (!is_array($this->cssMap))
        {
            throw new CException(Y::t('ags',__CLASS__.'\'s param $cssMap is not configured correctly'));
        }
    }

    public function getCss($clientFile)
    {
        /*
         * compute filename
         * assign special filename to special browser
         * so later could generate special css for it
         */
        if (!file_exists($this->_cssPath.'/'.$this->getCssFileName($clientFile)))
        {
            $this->genCssFile($clientFile);
        }
        return $this->getCssFileName($clientFile);
    }
    /**
     * remove all generated css files and regenerate
     */
    public function updateCss()
    {
    	foreach ($this->cssMap as $clientFile=>$config)
    	{
    		$this->cleanCssFile($clientFile);
    		if (is_array($config['specialBrowsers']))
    		{
    			foreach ($config['specialBrowsers'] as $k=>$v)
    			{
    				if (is_array($v))
    				{
    					$this->_browserEngine = $k;
    					foreach ($v as $version)
    					{
    						$this->_browserMVer = $version;
    						$this->_cssClientFiles[$clientFile] = null;
    						$this->genCssFile($clientFile);
    					}
    				}
    				else
    				{
    					$this->_browserEngine = $v;
    					$this->_browserMVer = null;
    					$this->_cssClientFiles[$clientFile] = null;
    					$this->genCssFile($clientFile);
    				}
    			}
    		}
    		/* gen the default one */
    		$this->_browserEngine = 'SOME_NON_EXIST_BROWSER_ENGINE';
            $this->_browserMVer = null;
            $this->_cssClientFiles[$clientFile] = null;
            $this->genCssFile($clientFile);
    	}
    	/* restore configs */
    	$this->detectBrowserEngineAndVer();
    }

    protected function detectBrowserEngineAndVer()
    {
    	$this->_browserMVer = (int)$this->_browser->majorver;
    	/* all IE.I'm not sure if IE9 will have new engine */
        if ('IE' === $this->_browser->browser && $this->_browserMVer < 9)
        {
            $this->_browserEngine = 'trident';
        }
        /* has Gecko and not "like Gecko" */
        elseif (preg_match('/(?<!like\s)Gecko/i',$this->_browser->browser_name_pattern))
        {
        	$this->_browserEngine = 'gecko';
        }
        /* has WebKit */
        elseif (false !== strpos($this->_browser->browser_name_pattern,'Webkit'))
        {
            $this->_browserEngine = 'webkit';
        }
        /* has KHTML and not "(KHTML*)" */
        elseif (preg_match('/(?<!\()KHTML/',$this->_browser->browser_name_pattern))
        {
            $this->_browserEngine = 'khtml';
        }
        else
        {
        	$this->_browserEngine = strtolower($this->_browser->browser);
        }
    }

    protected function getCssFileName($clientFile)
    {
        if (!$this->_cssClientFiles[$clientFile])
        {
	        $this->_cssClientFiles[$clientFile] = $clientFile.'-'.$this->detectBrowserType($clientFile);
	        if (YII_DEBUG)
	        {
	            /*
	             * in developing mode
	             * detect the last modify time of all to-merge css files
	             * and append it to the generated file
	             * so when we modified the source css files
	             * the geerated file will be updated automaticly
	             */
	            $mtimes = array();
	            foreach ($this->cssMap[$clientFile]['files'] as $cssFile)
	            {
	                $mtimes[] = filemtime($this->_cssPath.'/'.$cssFile);
	            }
	            $this->_cssClientFiles[$clientFile] .= '-'.max($mtimes).'.css';
	        }
	        else
	        {
	            /*
	             * in production mode
	             * let's do the update job manually
	             * to save user loading time
	             */
	            $this->_cssClientFiles[$clientFile] .= '.css';
	        }
        }
        return $this->_cssClientFiles[$clientFile];
    }

    protected function detectBrowserType($clientFile)
    {
        /*
         * match something like 'specialBrowsers'=>array('trident','Opera')
         */
        if (in_array($this->_browserEngine,$this->cssMap[$clientFile]['specialBrowsers']))
        {
            return $this->_browserEngine;
        }
        /*
         * like 'specialBrowsers'=>array('trident'=>array(6,7,8))
         */
        elseif (is_array($browser = $this->cssMap[$clientFile]['specialBrowsers'][$this->_browserEngine]))
        {
            $ver = $this->_browserMVer;
            if (!in_array($ver,$browser))
            {
                /*
                 * if version of current browser is not given in config array
                 * first search down for a nearest version
                 * then search up
                 */
            	$notfound = true;
            	for ($i = $ver;$i>=min($browser);$i--)
                {
                    if (in_array($i,$browser))
                    {
                        $ver = $i;
                        $notfound = false;
                        break;
                    }
                }
                if ($notfound)
                {
                    for ($i = $ver;$i<=max($browser);$i++)
                    {
                        if (in_array($i,$browser))
                        {
                            $ver = $i;
	                        $notfound = false;
	                        break;
                        }
                    }
                }
                if ($notfound)
                {
                    /* something is wrong if we got here */
                	throw new CException(Y::t('ags','Could not detemin finename for {browser}{ver}',array(
	                    '{browser}'=>$this->_browserEngine,
	                    '{ver}'=>$ver,
	                )));
                }
            }
            return $this->_browserEngine.'-'.$ver;
        }
        /*
         * thank stars
         */
        else
        {
            return 'default';
        }
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
                ),$clientFile
            ))) continue;

            $propertities = explode(';',$propertyTmp);
            foreach ($propertities as $propertyStr)
            {
                list($property,$value) = explode(':',$propertyStr);
                /* if it is not a property or not use for current user agent,ignore it */
                if ('' === ($property = $this->browserCssHackFilter(
                    preg_replace('/[\s\n\r]+/','',$property),$clientFile,true)
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
        file_put_contents($this->_cssPath.'/'.$this->getCssFileName($clientFile),$cssContent);
    }

    protected function cleanCssFile($clientFile)
    {
        if ($dir = opendir($this->_cssPath))
        {
            while ($file = readdir($dir))
            {
                /* if it starts with the basefilename like "base-default",it's to remove */
                if (0 === strpos($file,$clientFile.'-'))
                {
                    unlink($this->_cssPath.'/'.$file);
                }
            }
        }
    }

    protected function browserCssHackFilter($rule,$clientFile,$isProperty=false)
    {
        /*
         * if the selector or property match some special format
         * it's some browser's hack
         * if it's right its show time now,return it as a normal rule
         * else,tell it should be ignore by return ''
         */
        if ($isProperty)
        {
            switch (substr($rule,0,1))
            {
                case '-':
                    /* enable when gecko is not treated specially or is gecko */
                    if ('moz-' === substr($rule,1,4))
                    {
                        if ((('default' === $this->detectBrowserType($clientFile))
                                && (!(isset($this->cssMap[$clientFile]['specialBrowsers']['gecko'])
                                    || in_array('gecko',$this->cssMap[$clientFile]['specialBrowsers']))) )
                            || ('gecko' === $this->_browserEngine))
                        {
                            return $rule;
                        }
                        else
                        {
                            return '';
                        }
                    }
                    /* enable when webkit is not treated speacilly or is webkit */
                    if ('webkit-' === substr($rule,1,7))
                    {
                        if ((('default' === $this->detectBrowserType($clientFile))
                                && (!(isset($this->cssMap[$clientFile]['specialBrowsers']['webkit'])
                                    || in_array('webkit',$this->cssMap[$clientFile]['specialBrowsers']))))
                            || ('webkit' === $this->_browserEngine))
                        {
                            return $rule;
                        }
                        else
                        {
                            return '';
                        }
                    }
                    /* enable when khtml is not treated speacilly or is khtml */
                    if ('khtml-' === substr($rule,1,6))
                    {
                        if ((('default' === $this->detectBrowserType($clientFile))
                                && (!(isset($this->cssMap[$clientFile]['specialBrowsers']['khtml'])
                                    || in_array('khtml',$this->cssMap[$clientFile]['specialBrowsers']))))
                            || ('khtml' === $this->_browserEngine))
                        {
                            return $rule;
                        }
                        else
                        {
                            return '';
                        }
                    }
                /* no break */
                /* IE 6 and below */
                case '_':
                    if (('trident' === $this->_browserEngine) && (7 > $this->_browserMVer))
                    {
                        return trim(substr($rule,1));
                    }
                    else
                    {
                        return '';
                    }
                break;
                /* IE 7 and below */
                case '*':
                    if (('trident' === $this->_browserEngine) && (8 > $this->_browserMVer))
                    {
                        return trim(substr($rule,1));
                    }
                    else
                    {
                        return '';
                    }
                break;
            }
        }
        else
        {
            /* IE 6 and below */
            if (0 === strpos($rule,'* html'))
            {
                if (('trident' === $this->_browserEngine) && (7 > $this->_browserMVer))
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
                if (('trident' === $this->_browserEngine) && (7 === $this->_browserMVer))
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
                if (('trident' !== $this->_browserEngine) || (6 < $this->_browserMVer))
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
                if (('trident' !== $this->_browserEngine) || (6 < $this->_browserMVer))
                {
                    return $rule;
                }
                else
                {
                    return '';
                }
            }
            /* Modern browsers only (not IE 7) */
            if (0 === strpos($rule,'html>/**/body'))
            {
                if (('trident' !== $this->_browserEngine) || (7 < $this->_browserMVer))
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