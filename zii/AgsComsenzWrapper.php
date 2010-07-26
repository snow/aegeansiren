<?php

class AgsComsenzWrapper extends CComponent
{
	public $comsenzClientPath;
	public $key;
	public $apiUrl;
	public $apiIp;
	public $appId;
	public $ppp;
	public $charset = 'utf8';

	public $connect;
	public $dbHost;
	public $dbUser;
	public $dbPassword;
	public $dbName;
	public $dbCharset = 'utf8';
	public $dbTablePre = '';
	public $dbConnect = true;

	public function init()
	{
		if (!($this->key
			&& $this->apiUrl
			&& $this->apiIp
			&& $this->appId
			&& ($this->connect?
				($this->dbHost && $this->dbUser && $this->dbPassword && $this->dbName):true)))
		{
			throw new CException('err:missingRequiredParams');
		}

		define('UC_KEY', $this->key);
		define('UC_API', $this->apiUrl);
		define('UC_IP', $this->apiIp);
		define('UC_APPID', $this->appId);
		define('UC_PPP', $this->ppp);
		define('UC_CHARSET', $this->charset);

		define('UC_CONNECT', $this->connect);
		define('UC_DBHOST', $this->dbHost);
		define('UC_DBUSER', $this->dbUser);
		define('UC_DBPW', $this->dbPassword);
		define('UC_DBNAME', $this->dbName);
		define('UC_DBTABLEPRE', $this->dbTablePre);
		define('UC_DBCHARSET', $this->dbCharset);
		define('UC_DBCONNECT', $this->dbConnect);
	}

	public function syncLogin($uid)
	{
		require_once Yii::getPathOfAlias('application.comsenzClient').'/client.php';
		return uc_user_synlogin($uid);
	}

	public function syncLogout()
	{
		require_once Yii::getPathOfAlias('application.comsenzClient').'/client.php';
		return uc_user_synlogout();
	}

	public function getUser($username,$isUid=false)
	{
		require_once Yii::getPathOfAlias('application.comsenzClient').'/client.php';
		return uc_get_user($username, $isUid);
	}

	public function checkUsername($username)
	{
		require_once Yii::getPathOfAlias('application.comsenzClient').'/client.php';
		return uc_user_checkname($username);
	}

	public function checkEmail($email)
	{
		require_once Yii::getPathOfAlias('application.comsenzClient').'/client.php';
		return uc_user_checkemail($email);
	}

	public function login($username,$password)
	{
		require_once Yii::getPathOfAlias('application.comsenzClient').'/client.php';
		return uc_user_login($username,$password);
	}
}