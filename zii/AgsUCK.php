<?php
/**
 * a component to generate unique char key for table
 * compare char [0-9a-zA-Z] with their ascii
 * 一个用于生成唯一的字符主键的组件
 * 比较字符 [0-9a-zA-Z]的ASCII码来决定主键
 *
 * @author snow@firebloom.cc
 *
 */
class AgsUCK extends CComponent
{
	public $uckMaxTable = 'AgsUniqueCharKeyMax';
	private $_cache = array();

	public function init()
	{
		if (!in_array($this->uckMaxTable,Y::a()->db->schema->tableNames))
		{
			throw new CException(Y::t('ags','err:requriedSchemaNotFound'));
		}
	}

	public function genKey($tableName,$columnName)
	{
		// TODO: rewrite to use cache to save max key instead using database
 		// 这个类即将被重写，用缓存来存储unique char key列的最大值，而不是用数据库
		$curMax = Y::a()->db->createCommand('select `max` from '.$this->uckMaxTable.' where tableName=:tn and columnName=:cn')
			->queryScalar(array(
				':tn'=>$tableName,
				':cn'=>$columnName,
			));

		return $curMax?$this->increaseCharKey($curMax):1;
	}

	protected function increaseCharKey($key)
	{
		// scan from end of the string
		// 从字符串末位开始扫描
		for ($i = (strlen($key)-1); $i > -1; $i--)
		{
			// +1
			$int = $this->char2int($key[$i])+1;
			// $char < z,done
			// 比z小，不必进位了
			if (62 > $int)
			{
				$key[$i] = $this->int2char($int);
				break;
			}
			//
			else
			{
				// higher position will be process in next loop
				// 下一轮循环会处理更高一位
				$key[$i] = 0;
				// already at start of string
				// 到最高位了
				if (0===$i)
				{
					$key = '1'.$key;
					break;
				}
			}
		}
		return $key;
	}

	/**
	 * @param char to get ascii code
	 * 获得字符的ascii
	 */
	protected function char2int($char)
	{
		$ord = ord($char);

		// [0-9]=>0-9
		if ((47 < $ord) && (58 > $ord))
		{
			return (int)$char;
		}
		// [A-Z]=>10-35
		elseif ((64 < $ord) && (91 > $ord))
		{
			return ($ord-55);
		}
		// [a-z]=>36-61
		elseif ((96 < $ord) && (123 > $ord))
		{
			return ($ord-61);
		}
		throw new CException('ags','err:invalidParams');
	}

	/**
	 * @param int to get char with ascii
	 * 获得该ascii的字符
	 */
	protected function int2char($int)
	{
		// 0-9=>[0-9]
		if ((0 <= $int) && (10 > $int))
		{
			return $int.'';
		}
		// 10-35=>[A-Z]
		elseif ((9 < $int) && (36 > $int))
		{
			return chr($int+55);
		}
		// 36-61=>[a-z]
		elseif ((35 < $int) && (62 > $int))
		{
			return chr($int+61);
		}
		throw new CException('ags','err:invalidParams');
	}
}