<?php
/**
 * this class make its subclass able to import into other apps
 * config:
 * mother app side:provide a config file contains at least db config like this:
 * <?php
 * return array(
 * 	'db'=>array(
 * 		'connectionString' => 'mysql:host=localhost;dbname=DB_NAME',
 * 		'username' => 'USERNAME',
 * 		'password' => 'PASSWORD',
 * 	),
 * );
 * and than return path to this file in public static function getAgsHjConfigFilePath();
 *
 * drop-in app side:define datab access mode or more config in application params
 * 'params'=>array( *
 * 		'helljumpers'=>array(
 * 			'HelljumperClassOne'=>array('dataAccessMode'=>'db'),
 * 			'HelljumperClassTwo'=>array('dataAccessMode'=>'soap'),
 * 		),
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

	/**
	 * store config for classes in $_agsHjConfig[className] = config_for_class format
	 * @var array
	 */
	private static $_agsHjConfig;
	/**
	 * store db connections in $_db[className] = dbConn_for_class format
	 * @var array
	 */
	private static $_db;

	abstract public static function getAgsHjConfigFilePath();

	public function __construct($scenario='insert')
	{
		// override this constructor to init helljumper config before every thing
		// use call_user_func() to dynamically call subclass-override
		// DO call parent::__construct() in override
		call_user_func(array($class=get_class($this),'initAgsHjConfigS'),$class);

		parent::__construct($scenario);
	}

	/**
	 * shortcut to getAgsHjConfigS()
	 * @param string $key
	 * @return array config
	 */
	protected function getAgsHjConfig($key = '')
	{
		return self::getAgsHjConfigS(get_class($this),$key);
	}

	/**
	 * get configs of a helljumper class
	 * omitting $key to get all config of a class
	 * or provide a key to get part of it
	 *
	 * @param string $class
	 * @param string $key
	 */
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

	/**
	 * shortcut to setAgsHjConfigS()
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	protected function setAgsHjConfig($key,$value)
	{
		self::setAgsHjConfigS(get_class($this),$key,$value);
	}

	/**
	 * set one of config items of a helljumper class
	 *
	 * @param string $class
	 * @param string $key
	 * @param mixed $value
	 */
	protected static function setAgsHjConfigS($class,$key,$value)
	{
		self::$_agsHjConfig[$class][$key] = $value;
	}

	/**
	 * override this method to make subclass logics	 *
	 * DO call parent::initAgsHjConfigS($class) in override
	 *
	 * @param string $class
	 */
	public static function initAgsHjConfigS($class)
	{
		if (null === self::$_agsHjConfig)
		{
			self::$_agsHjConfig = array();
		}

		if (!isset(self::$_agsHjConfig[$class]))
		{
			if (file_exists($file=call_user_func(array($class,'getAgsHjConfigFilePath'))))
			{
				self::$_agsHjConfig[$class] = include($file);
				// Y::p('helljumper')[$class] exists means this AR is droped outside
				if (($clientHelljumperConfig = Y::p('helljumpers')) && isset($clientHelljumperConfig[$class]))
				{
					//merge mother-side config and then drop-in-side's
					self::$_agsHjConfig[$class] = array_merge(self::$_agsHjConfig[$class],$clientHelljumperConfig[$class]);
				}
				// AgsAR metadata
				if (isset(self::$_agsHjConfig[$class]['agsMetadataColumn']))
				{
					$this->setAgsMetaColumn(self::$_agsHjConfig[$class]['agsMetadataColumn']);
				}
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

	/**
	 * override to make possible to use mother-app-side database connection.
	 * @return CDbConnection the database connection used by active record.
	 */
	public function getDbConnection()
	{
		switch ($this->getAgsHjConfig('dataAccessMode'))
		{
			case self::DATA_ACCESS_DB:
				if (null === self::$_db)
				{
					self::$_db = array();
				}
				if (!(self::$_db[$class=get_class($this)] instanceof CDbConnection))
				{
					self::$_db[$class] = new CDbConnection;

					foreach ($this->getAgsHjConfig('db') as $key=>$value)
					{
						self::$_db[$class]->$key = $value;
					}

					self::$_db[$class]->active = true;
				}
				return self::$_db[$class];
			break;

			default:
				return parent::getDbConnection();
			break;

			case self::DATA_ACCESS_SOAP:
				throw new CException('err:incomplete');
			break;
		}
	}
}