<?php

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
		$curMax = Y::a()->db->createCommand('select `max` from '.$this->uckMaxTable.' where tableName=:tn and columnName=:cn')
			->queryScalar(array(
				':tn'=>$tableName,
				':cn'=>$columnName,
			));
		
		$newKey = $curMax?'':1;
	}
	
	protected function increaseCharKey($key)
	{
		for ($i = (strlen($key)-1); $i > -1; $i--)
		{
			$int = $this->char2int($key[$i])+1;
			if (62 > $int)
			{
				$key[$i] = $this->int2char($int);
				break;
			}
			else
			{
				$key[$i] = 0;
				if (0===$i)
				{
					$key = '1'.$key;
				}
			}
		}
		return $key;
	}
	
	protected function char2int($char)
	{
		$ord = ord($char);
		
		if ((47 < $ord) && (58 > $ord))
		{
			return (int)$char;
		}
		elseif ((64 < $ord) && (91 > $ord))
		{
			return ($ord-55);
		}
		elseif ((96 < $ord) && (123 > $ord))
		{
			return ($ord-61);
		}
		throw new CException('ags','err:invalidParams');
	}
	
	protected function int2char($int)
	{
		if ((0 <= $int) && (10 > $int))
		{
			return $int.'';
		}
		elseif ((9 < $int) && (36 > $int))
		{
			return chr($int+55);
		}
		elseif ((35 < $int) && (62 > $int))
		{
			return chr($int+61);
		}
		throw new CException('ags','err:invalidParams');
	}
}