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
}