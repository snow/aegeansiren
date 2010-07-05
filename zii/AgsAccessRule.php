<?php

class AgsAccessRule
{
	private $_childRules = array();
	private $_rule;
	private $_nextRule = false;

	function __construct($rule)
	{
		$rule = trim($rule);
		$this->extractRule($rule);
	}

	private function extractRule($rule)
	{
		$ruleSegments = array();
		$ruleOperators = array();
		$ruleLen = strlen($rule);
		$indicator = 0;
		for ($i = 0;$i<$ruleLen;$i++)
		{
			switch ($rule[$i])
			{
				default:
				break;

				case '+':
				case ',':
					if ($indicator<$i)
					{
						$ruleSegments[] = substr($rule,$indicator,$i-$indicator);
						$indicator = $i+1;
					}
					$ruleOperators[] = $rule[$i];
				break;

				case '(':
					$nestedBracketCount = 0;
					for ($j = $i+1;$j<$ruleLen;$j++)
					{
						if ('(' === $rule[$j])
						{
							$nestedBracketCount++;
						}
						elseif (')' === $rule[$j])
						{
							if (0 === $nestedBracketCount)
							{
								$breaketEnd = $j;
								break;
							}
							else
							{
								$nestedBracketCount--;
							}
						}
						if ($ruleLen === $j)
						{
							throw new AgsInvalidParamsException(__CLASS__.'::'.__FUNCTION__,array('invalidRule'=>$rule));
						}
					}
					$ruleSegments[] = substr($rule,$i+1,$breaketEnd-$i-1);
					$i = $breaketEnd;
					$indicator = $i+2;
				break;
			}

			if (($ruleLen-1 === $i) && ($indicator < $ruleLen))
			{
				$ruleSegments[] = substr($rule,$indicator);
			}
		}

		$segCount = count($ruleSegments);
		if (1 === $segCount)
		{
			$this->_rule = current($ruleSegments);
		}
		else
		{
			if ($firstOr = array_search(',',$ruleOperators))
			{
				$endChildSegIndex = $firstOr+1;
			}
			else
			{
				$endChildSegIndex = $segCount;
			}

			for ($i = 0; $i < $endChildSegIndex; $i++)
			{
				$this->_childRules[] = new AgsAccessRule(array_shift($ruleSegments));
			}

			if ($firstOr)
			{
				$nextRule = array_shift($ruleSegments);
				for (;$i < count($ruleOperators);$i++)
				{
					$nextRule .= $ruleOperators[$i].array_shift($ruleSegments);
				}

				$this->_nextRule = new AgsAccessRule($nextRule);
			}
		}
	}

	public function execRule()
	{
		$success = false;
		if (count($this->_childRules))
		{
			$success = true;
			foreach ($this->_childRules as $rule)
			{
				$success = $success && $rule->execRule();
			}
		}
		else
		{
			list($ruleName,$ruleParamSrl) = explode(':',$this->_rule);
			$ruleParams = explode(',',$ruleParamSrl);
			Y::l($ruleName,'debug');
			Y::l($ruleParamSrl,'debug');
			switch ($ruleName)
			{
				case 'role':
					return isset(Y::u()->role) && in_array(Y::u()->role,$ruleParams);
				break;

				case 'privileges':
					return isset(Y::u()->privilegeAr) && count(array_intersect($ruleParams,Y::u()->privilegeAr));
				break;

				case 'users':
					return (in_array('@',$ruleParams) && (!Y::u()->isGuest))
						|| (isset(Y::u()->username) && in_array(Y::u()->username,$ruleParams));
				break;

				default:
					return false;
				break;
			}
		}

		return $success || ($this->_nextRule && $this->_nextRule->execRule());
	}
}