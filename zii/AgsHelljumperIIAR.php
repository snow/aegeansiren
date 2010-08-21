<?php
/**
 * this class make its subclass able to import into other apps
 * useage:
 * 'components'=>array(
 * 	'classId'=>array(
 * 		'class'=>'alias.to.your.subclass',
 * 		'dataAccessMode'=>'db', //or soap
 * 	),
 * ),
 *
 * @author snow
 *
 */
abstract class AgsHelljumperIIAR extends AgsAR
{
	const DATA_ACCESS_NATIVE = 'native';
	const DATA_ACCESS_DB = 'db';
	const DATA_ACCESS_SOAP = 'soap';

	private static $_agsHjConfig;

	abstract protected static function getAgsHjConfigFilePath();

	public function __construct($scenario='insert')
	{
		$this->initAgsHjConfig();

		parent::__construct($scenario);
	}

	protected function getAgsHjConfig($key = '')
	{
		return self::getAgsHjConfigS(get_class($this),$key);
	}

	protected static function getAgsHjConfigS($class,$key = '')
	{
		if ($key)
		{
			return self::$_agsHjConfig[$class][$key];
		}
		else
		{
			return self::$_agsHjConfig[$class];
		}
	}

	protected function setAgsHjConfig($key,$value)
	{
		self::setAgsHjConfigS(get_class($this),$key,$value);
	}

	protected static function setAgsHjConfigS($class,$key,$value)
	{
		self::$_agsHjConfig[$class][$key] = $value;
	}

	protected function initAgsHjConfig()
	{
		self::initAgsHjConfigS(get_class($this));
	}

	protected static function initAgsHjConfigS($class)
	{
		if (null === self::$_agsHjConfig)
		{
			self::$_agsHjConfig = array();
		}

		if (!isset(self::$_agsHjConfig[$class = get_class($this)]))
		{
			// Y::p('helljumper')[$class] exists means this AR is droped outside
			if (($clientHelljumperConfig = Y::p('helljumpers')) && isset($clientHelljumperConfig[$class]))
			{
				if (file_exists(self::getAgsHjConfigFilePath()))
				{
					//merge server side config and then client side config
					self::$_agsHjConfig[$class] = array_merge(include(self::getAgsHjConfigFilePath()),$clientHelljumperConfig[$class]);
					//$this->config now available
				}
				else
				{
					throw new CException('err:missingConfig:'.$class);
				}

				switch (self::$_agsHjConfig[$class]['dataAccessMode'])
				{
					case self::DATA_ACCESS_DB:
						if (!is_array(self::$_agsHjConfig[$class]['db']))
						{
							throw new CException('err:invalidDbConfig:'.$class);
						}
					break;

					case self::DATA_ACCESS_SOAP:
						throw new CException('err:incomplete');
					break;
				}
			}
		}
	}

	/**
	 * override to use Shangrila's database connection.
	 * @return CDbConnection the database connection used by active record.
	 */
	public function getDbConnection()
	{
		switch ($this->getAgsHjConfig('dataAccessMode'))
		{
			case self::DATA_ACCESS_DB:
				if (!(($dbConn = $this->getAgsHjConfig('dbConn')) instanceof CDbConnection))
				{
					$dbConn = new CDbConnection;

					foreach ($this->getAgsHjConfig('db') as $key=>$value)
					{
						$dbConn->$key = $value;
					}

					$dbConn->active = true;
					$this->setAgsHjConfig('dbConn',$dbConn);
				}
				return $dbConn;
			break;

			case self::DATA_ACCESS_NATIVE:
				return parent::getDbConnection();
			break;

			case self::DATA_ACCESS_SOAP:
				throw new CException('err:incomplete');
			break;
		}
	}
}