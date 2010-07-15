<?php

class AgsComsenzWrapper extends CComponent
{
	public $comsenzClientPath;
	public $connect = '';
	public $key;
	public $apiUrl;
	public $apiIp;
	public $charset = 'utf8';
	public $appId;
	
	public $dbHost;
	public $dbUser;
	public $dbPassword;
	public $dbName;
	public $dbCharset = 'utf8';
	public $dbTablePre = '';
	public $dbDbConnect = true;
	
	public function init()
	{
		if (!($this->comsenzClientPath
			&& $this->key
			&& $this->apiUrl
			&& $this->apiIp
			&& $this->appId
			&& ($this->connect?
				($this->dbHost && $this->dbUser && $this->dbPassword && $this->dbName):true)))
		{
			throw new CException('err:needed');
		}
		
		require_once Yii::getPathOfAlias('application.comsenzClient').'/client.php';
	}
	
	public function syncLogin($uid)
	{
		return uc_user_synlogin($uid);
	}
	
	public function syncLogout()
	{
		return uc_user_synlogout();
	}
	
	public function getUser($username,$isUid=false)
	{
		return uc_get_user($username, $isUid);
	}
}