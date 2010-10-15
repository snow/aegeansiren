<?php
/**
 * utilities
 * 各种小工具
 *
 * @author snow@firebloom.cc
 */
class S
{
	const AFTER_VARDUMP_CONTINUE = 0;
	const AFTER_VARDUMP_TERMINATE = 1;

	/**
	 * snippets use with var_dump
	 * 经常和var_dump一起用的代码
	 *
	 * @param unknown_type $var
	 * @param unknown_type $afterDebug
	 */
	public static function d($var,$afterDebug = self::AFTER_VARDUMP_TERMINATE)
	{
		echo '<pre>';

		var_dump($var);

		if (self::AFTER_VARDUMP_TERMINATE === $afterDebug)
		{
			exit('</pre>');
		}
	}

	/**
	 * 获得一个单词的复数形式
	 *
	 * @param string $word to get multiform
	 */
	public static function getMultiform($word)
	{
		if (preg_match('/[^aeiou]y$/',$word))
		{
			return preg_replace('/y$/','ies',$word);
		}
		else
		{
			return $word.'s';
		}
	}

	/**
	 * 获得一个单词的单数形式
	 *
	 * @param string $word to get single form
	 */
	public static function getSingleForm($word)
	{
		if (preg_match('/ies$/',$word))
		{
			return preg_replace('/ies$/','y',$word);
		}
		else
		{
			return substr($word,0,-1);
		}
	}

	/**
	 * @param string to determain if is email address
	 */
	public static function isEmail($string)
	{
		$pattern='/^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*'
			.'@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/';
		return preg_match($pattern,$string);
	}

	/**
	 * @param string to determain if is Tencent qq
	 */
	public static function isTencentQq($string)
	{
		return is_numeric($string)?(($len = strlen($string)) >= 5 && $len <= 10):self::isEmail($string);
	}

	/**
	 * @param string to determin if is ip address
	 */
	public static function isIpAddress($string)
	{
		return (false!==inet_pton($string));
	}

	/**
	 * @param string url to extract domain from
	 */
	public static function getUrlDomain($url)
	{
		return is_string($url)?current(explode('/',array_pop(explode('//',$url,2)),2)):'';
	}

	/**
	 * 生成随机字符串
	 *
	 * @param int len of the random string
	 * @param string gen method:basic,alpah,alnum,numeric,nozero,unique,md5
	 */
	public static function getRandomString($len = 8,$type = 'alnum')
	{
		switch($type)
		{
			case 'basic':
				return mt_rand();
			break;

			case 'alnum':
			case 'numeric':
			case 'nozero':
			case 'alpha':
				switch ($type)
				{
					case 'alpha':
						$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
					break;
					case 'alnum':
						$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
					break;
					case 'numeric':
						$pool = '0123456789';
					break;
					case 'nozero':
						$pool = '123456789';
					break;
				}

				$str = '';
				for ($i=0; $i < $len; $i++)
				{
					$str .= substr($pool, mt_rand(0, strlen($pool) -1), 1);
				}
				return $str;
			break;

			case 'unique':
			case 'md5':
				return md5(uniqid(mt_rand()));
			break;
		}
	}

	/**
	 * DANGEROUS! delete dir
	 * 危险！删除文件夹
	 * @param path to delete
	 */
	public static function delDir($dir)
	{
		if (substr($dir, strlen($dir)-1, 1) != '/') $dir .= '/';

		if ($handle = opendir($dir))
		{
			while ($obj = readdir($handle))
			{
				if ($obj != '.' && $obj != '..')
				{
					if (is_dir($dir.$obj))
					{
						if (!self::delDir($dir.$obj)) return false;
					}
					elseif (is_file($dir.$obj))
					{
						if (!unlink($dir.$obj)) return false;
					}
				}
			}

			closedir($handle);

			return @rmdir($dir);
		}
		return false;
	}
}