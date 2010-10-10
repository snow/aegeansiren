<?php
/**
 *
 * define access role in action like this:
 * 在action内定义访问控制规则，形如：
 *
 * $this->accessControl('(users:dragon,makelu,snow+roles:admin,superman)|roles:root');
 * $this->accessControl('ips:127.0.0.1,8.8.8.8');
 *
 * available rules:
 * roles: success if one of given roles found in Y::u()->roles
 * privileges: success if Y::u()->hasPrivilege() return true with one of given
 * users: success if Y::u()->username is one of given,or success if user is authed with users:@
 * ips: success if visitor ip is in given list
 * compareUserIdWith: compare Y::()->id with given value
 *
 * 可用规则：
 * roles: 当给出的角色中有至少一个在Y::u()->roles中找到时通过
 * privileges: 当给出权限之一通过 Y::u()->hasPrivilege()检查时通过
 * users: 当Y::u()->username在给出的列表中时，或者用户已登录且给出的用户名为@时通过
 * ips: 当访问者的ip在给出的列表中时通过
 * compareUserIdWith: 比较Y::u()->id和给出的值，相等则通过
 *
 * @author snow@firebloom.cc
 *
 */
class AgsAccessRule
{
	private $_childRules = array();
	private $_rule;
	private $_nextRule;

	const ROOT_USER_ID = 1;

	function __construct($rule)
	{
		$this->extractRule($rule);
	}

	/**
	 * extract string into executable segments
	 *
	 * @param string $rule
	 */
	private function extractRule($rule)
	{
		$ruleSegments = array();
		$ruleOperators = array();
		$ruleLen = strlen($rule);
		// index of proceed
		$iop = 0;

		// scan the rule string char by char $ios index of scanned
		for ($ios = 0;$ios<$ruleLen;$ios++)
		{
			switch ($rule[$ios])
			{
				case '&':
				case '|':
					if ($iop<$ios)
					{
						$ruleSegments[] = trim(substr($rule,$iop,$ios-$iop));
						$iop = $ios+1;
					}
					$ruleOperators[] = $rule[$ios];
				break;

				case '(':
					$nestedBracketCount = 0;
					for ($j = $ios+1;$j<$ruleLen;$j++)
					{
						switch ($rule[$j])
						{
							case ')':
								if (0 === $nestedBracketCount)
								{
									$breaketEnd = $j;
									break;
								}
								else
								{
									$nestedBracketCount--;
								}
							break;

							case '(':
								$nestedBracketCount++;
							break;
						}

						if ($ruleLen === $j)
						{
							throw new AgsInvalidParamsException(__CLASS__.'::'.__FUNCTION__,array('invalidRule'=>$rule));
						}
					}
					// push entire content of the outtest breaket but itself into segments
					$ruleSegments[] = trim(substr($rule,$ios+1,$breaketEnd-$ios-1));
					// move ios to breaket end
					$ios = $breaketEnd;
					// +2 cuz the ')' and the operater after ')'
					$iop = $ios+2;
				break;
			}

			// reached string end
			// 抵达访问控制规则末端
			if ((($ruleLen-1 === $ios) && ($iop < $ruleLen))
				// the rest of first OR operator will be put in $this->_nextRule
				// so OR may be either the last orperator or not exist
				// 如果遇到 OR 操作符，那么控制规则剩下的部分都会放进 $this->_nextRule
				// 所以OR操作符要么是最后一个操作符要么不存在
				|| ('|' === $rule[$ios]))
			{
				$ruleSegments[] = trim(substr($rule,$iop));
				// leave scan loop
				break;
			}
		}

		$segCount = count($ruleSegments);
		// the leaf node of rules tree,has one and only one executable rule
		// 规则树的末端节点，有且只有一条可执行的规则
		if (1 === $segCount)
		{
			$this->_rule = current($ruleSegments);
		}
		// or split down into child
		// 否则继续分解
		else
		{
			// if the last operator is OR,the last rule should be proceed later
			// or all rules should be put in children
			// 如果最后一个操作符是OR，最后一条规则后面再处理
			// 否则所有的规则都放进子节点
			for ($i = 0; $i < (('|' === end($ruleOperators))?($segCount-1):$segCount); $i++)
			{
				$this->_childRules[] = new AgsAccessRule(array_shift($ruleSegments));
			}

			// if still segment remain,it must be the OR rule
			// 如果还有规则段剩下，必然是那条OR规则了
			if (count($ruleSegments))
			{
				$this->_nextRule = new AgsAccessRule(array_pop($ruleSegments));
			}
		}
	}

	/**
	 * the internal layer of excution,aim to check the rule segment logic
	 */
	protected function execInternal()
	{
		if (self::ROOT_USER_ID == Y::u()->id)
		{
			return true;
		}
		else
		{
			// if $this has childRules,$this is not a leaf node of rule tree,just run into children
			// 如果$this有子节点，那么执行子节点即可
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
			// its leaf node,execute owned rule
			// 是字节点，执行自己的规则
			else
			{
				list($ruleName,$ruleParamSrl) = explode(':',$this->_rule);
				$ruleParams = explode(',',$ruleParamSrl);
				switch ($ruleName)
				{
					case 'roles':
						return !Y::u()->isGuest && isset(Y::u()->roles) && count(array_intersect($ruleParams,Y::u()->roles));
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

					case 'compareUserIdWith':
						try
						{
							return (!Y::u()->isGuest && ($ruleParamSrl == Y::u()->id));
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

	/**
	 *
	 */
	public function exec()
	{
		return $this->execInternal() || ($this->_nextRule && $this->_nextRule->exec());
	}
}