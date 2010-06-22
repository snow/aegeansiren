<?php
class AgsSingedinUserOnlyFilter extends CFilter
{
	protected function preFilter($filterChain)
	{
		return !Y::a()->user->isGuest;
	}
}