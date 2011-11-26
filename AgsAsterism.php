<?php
/**
 * provide asterism const and utility methods
 *
 * @author rasse8574@gmail.com,snow@firebloom.cc
 *
 */
class AgsAsterism{
	const ARIES = 'aries';
	const TAURUS = 'taurus';
	const GEMINI = 'gemini';
	const CANCER = 'cancer';
	const LEO = 'leo';
	const VIRGO = 'virgo';
	const LIBRA = 'libra';
	const SCORPIO = 'scorpio';
	const SAGITTARIUS = 'sagittarius';
	const CAPRICORN = 'capricorn';
	const AQUARIUS = 'aquarius';
	const PISCES = 'pisces';

	public static function determinAsterismFromDay($day){
		// 可以解析的格式
		$dateformats = array(
			'/^\d{2,4}[-\.\/](\d{1,2})[-\.\/](\d{1,2})/',	// 有年份以 - . / 分隔的日期
			'/^(\d{1,2})[-\.\/](\d{1,2})/'	// 无年份以 - . / 分隔的日期
		);
		// 逐一判断是否是可解析的格式
		$iscorrect = false;
		foreach($dateformats as $key => $value){
			$match = NULL;
			preg_match($value, $day, $match);
			if(sizeof($match) == 3){
				$iscorrect = true;
				break;
			}
		}
		// 不是可解析的返回 bad date
		if(!$iscorrect || $match[1] > 12 || $match[2] > 31){
			throw new CException(Y::t('ags','err:invalidParams',array(
				'{where}'=>__CLASS__.'::'.__FUNCTION__,
				'{paramInfo}'=>$day,
			)));
		}
		// 组合成小数点格式的月+日
		$date = $match[1] + $match[2]/100;
		// 12月21日为分界线, 过后的时间月份从零开始计数
		$date = $date > 12.21 ? $date - 12 : $date;
		// 存储星座分界信息
		$monthdata = array(
			array(0.22, 1.19, self::CAPRICORN),
			array(1.20, 2.18, self::AQUARIUS),
			array(2.19, 3.20, self::PISCES),
			array(3.21, 4.20, self::ARIES),
			array(4.21, 5.20, self::TAURUS),
			array(5.21, 6.21, self::GEMINI),
			array(6.22, 7.22, self::CANCER),
			array(7.23, 8.22, self::LEO),
			array(8.23, 9.22, self::VIRGO),
			array(9.23, 10.22, self::LIBRA),
			array(10.23, 11.21, self::SCORPIO),
			array(11.22, 12.21, self::SAGITTARIUS),
		);
		// 跟据月份数据判断范围, 星座最多只需要判断两个分界点
		$checkdata = array_slice($monthdata, intval($date-1), 2);
		foreach($checkdata as $key=>$value){
			if($date >= $value[0] && $date <= $value[1]){
				break;
			}
		}
		return $checkdata[$key][2];
	}
}