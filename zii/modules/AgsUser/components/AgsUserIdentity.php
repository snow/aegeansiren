<?php

/**
 * AgsUserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class AgsUserIdentity extends CUserIdentity
{
	public $id;
	public $model;

	public function __construct()
	{
	}

	public function getId()
	{
		return $this->id;
	}
}