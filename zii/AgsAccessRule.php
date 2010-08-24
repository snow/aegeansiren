<?php
/**
 *
 * define access role in action like this:
 *
 * $this->accessControl('(user:dragon,makelu,snow+roles:admin,superman)|roles:root');
 * $this->accessControl('ips:127.0.0.1,8.8.8.8');
 *
 * @author snow
 *
 */
class AgsAccessRule
{
	private $_childRules = array();
	private $_rule;
	private $_nextRule;

	function __construct($rule)
	{
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

				case '&':
				case '|':
					if ($indicator<$i)
					{
						$ruleSegments[] = trim(substr($rule,$indicator,$i-$indicator));
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
					$ruleSegments[] = trim(substr($rule,$i+1,$breaketEnd-$i-1));
					$i = $breaketEnd;
					$indicator = $i+2;
				break;
			}

			if (($ruleLen-1 === $i) && ($indicator < $ruleLen))
			{
				$ruleSegments[] = trim(substr($rule,$indicator));
			}
		}

		$segCount = count($ruleSegments);
		if (1 === $segCount)
		{
			$this->_rule = current($ruleSegments);
		}
		else
		{
			if (false !== ($firstOr = array_search('|',$ruleOperators)))
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

	protected function execInternal()
	{
		if (1==Y::u()->id/*isset(Y::u()->roles) && is_array(Y::u()->roles) && in_array('AgsRoot',Y::u()->roles)*/)
		{
			return true;
		}
		else
		{
			if (count($this->_childRules))
			{
				foreach ($this->_childRules as $rule)
				{
					if (!$rule->exec())
					{
						return false;
					}
				}
				return true;
			}
			else
			{
				list($ruleName,$ruleParamSrl) = explode(':',$this->_rule);
				$ruleParams = explode(',',$ruleParamSrl);
				switch ($ruleName)
				{
					case 'roles':
						return isset(Y::u()->roles) && count(array_intersect($ruleParams,Y::u()->roles));
					break;

					case 'privileges':
						if (method_exists(Y::u(),hasPrivilege))
						{
							foreach ($ruleParams as $privilege)
							{
								if (Y::u()->hasPrivilege($privilege))
								{
									return true;
								}
							}
						}
						return false;
					break;

					case 'users':
						return (in_array('@',$ruleParams) && (!Y::u()->isGuest))
							|| (isset(Y::u()->username) && in_array(Y::u()->username,$ruleParams));
					break;

					case 'ips':
						return in_array(Y::r()->userHostAddress,$ruleParams);
					break;

					case 'checkId':
						try
						{
							return (!Y::u()->isGuest && (Y::a()->controller->loadModel()->$ruleParamSrl == Y::u()->id));
						}
						catch (Exception $e)
						{
							return false;
						}
					break;
				}
			}
		}
	}

	public function exec()
	{
		return $this->execInternal() || ($this->_nextRule && $this->_nextRule->exec());
	}
}