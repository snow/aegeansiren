<?php
/**
 * alias frequently used Yii components or methods with a short name
 * 常用Yii框架组件或者方法的快捷方式
 *
 * @author snow@firebloom.cc
 *
 */
class Y
{
	public static function t($category,$message,$params=array(),$source=null,$language=null)
	{
		return Yii::t($category,$message,$params,$source,$language);
	}

	public static function e($category,$message,$params=array(),$source=null,$language=null)
	{
		echo self::t($category,$message,$params,$source,$language);
	}

	public static function l($msg,$level=CLogger::LEVEL_INFO,$category='application')
	{
		Yii::log($msg,$level,$category);
	}

	public static function d($msg,$category='application')
	{
		Yii::trace($msg,$category);
	}

	public static function a()
	{
		return Yii::app();
	}

	public static function u()
	{
		return self::a()->user;
	}

	public static function r()
	{
		return self::a()->request;
	}

	public static function p($key)
	{
		return isset(self::a()->params[$key])?self::a()->params[$key]:null;
	}
}