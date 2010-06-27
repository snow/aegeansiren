<?php
class AgsSignedinUserOnlyFilter extends CFilter
{
	protected function preFilter($filterChain)
	{
		return !Y::a()->user->isGuest;
	}
}