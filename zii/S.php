<?php
/**
 * alias of AegeanSiren
 */
class S
{
	public static function d($var)
	{
		echo '<pre>';
		var_dump($var);
		echo '</pre>';exit;
	}

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

	public static function isEmail($string)
	{
		$pattern='/^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*'
			.'@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/';
		return preg_match($pattern,$string);
	}

	public static function getUrlDomain($url)
	{
		return is_string($url)?current(explode('/',array_pop(explode('//',$url,2)),2)):'';
	}

	public static function getRandomString($type = 'alnum', $len = 8)
	{
		switch($type)
		{
			case 'basic'	: return mt_rand();
			  break;
			case 'alnum'	:
			case 'numeric'	:
			case 'nozero'	:
			case 'alpha'	:

					switch ($type)
					{
						case 'alpha'	:	$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
							break;
						case 'alnum'	:	$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
							break;
						case 'numeric'	:	$pool = '0123456789';
							break;
						case 'nozero'	:	$pool = '123456789';
							break;
					}

					$str = '';
					for ($i=0; $i < $len; $i++)
					{
						$str .= substr($pool, mt_rand(0, strlen($pool) -1), 1);
					}
					return $str;
			  break;
			case 'unique'	:
			case 'md5' 		:

						return md5(uniqid(mt_rand()));
			  break;
			case 'encrypt'	:
			case 'sha1'	:

						$CI =& get_instance();
						$CI->load->helper('security');

						return do_hash(uniqid(mt_rand(), TRUE), 'sha1');
			  break;
		}
	}
}