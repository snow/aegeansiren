<?php
class AgsSeraph
{
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
}