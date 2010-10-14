<?php
/**
 * an application define subclass
 * then other applications could import that class to access data cross application
 * 一个应用实现此类的子类
 * 那么其它应用就可以通过import那个子类来实现跨应用的数据访问
 *
 * WARNING!avoid application-dependence as it will be drop in other app
 * 警告！避免依赖子类所属应用的代码，因为要被import到其它应用
 *
 * config:
 * 设置
 * mother app side:provide a config file contains at least db config like this:
 * 所属应用側：提供一个配置文件包含数据库链接，其它更多的信息是可选的
 * <?php
 * return array(
 * 	'db'=>array(
 * 		'connectionString' => 'mysql:host=localhost;dbname=DB_NAME',
 * 		'username' => 'USERNAME',
 * 		'password' => 'PASSWORD',
 * 	),
 * );
 * and than return path to this file in {@link getAgsHjConfigFilePath};
 * 在getAgsHjConfigFilePath()方法中返回这个文件的路径
 *
 * drop-in app side:define datab access mode or more config in application params
 * 引用测：在应用config的params中定义数据库访问模式，其它更多的配置是可选的，并且会覆盖mother app側的配置
 * 'params'=>array( *
 * 		'helljumpers'=>array(
 * 			'HelljumperClassOne'=>array('dataAccessMode'=>'db'),
 * 			'HelljumperClassTwo'=>array('dataAccessMode'=>'soap'),
 * 		),
 * ),
 *
 * @author snow@firebloom.cc
 *
 */
abstract class AgsHelljumperIIAR extends AgsAR
{
	const DATA_ACCESS_NATIVE = 'native';
	const DATA_ACCESS_DB = 'db';
	const DATA_ACCESS_SOAP = 'soap';

	/**
	 * store config for classes in $_agsHjConfig[agsHjId] = array $config_for_class format
	 * 在内存中用为每一个有agsHjId的helljumper的子类存储配置。格式为 $_agsHjConfig[agsHjId] = 配置数组
	 * @var array
	 */
	private static $_agsHjConfig = array();

	/**
	 * store db connections in $_db[agsHjId] = CDbConnection $db format
	 * 在内存中用为每一个agsHjId的helljumper的子类存储数据库连接。格式为 $_agsHjConfig[agsHjId] = CDbConnection $db
	 * @var array
	 */
	private static $_db = array();

	/**
	 * @return string filepath of config file
	 * 返回配置文件路径
	 */
	abstract public static function getAgsHjConfigFilePath();

	/**
	 * when helljumper subclass A is inherited as B
	 * whether class B has it's own config or use A's?
	 * only when class B's own getAgsHjId() returned a value diff from parent,it has it's own conf
	 * 当helljumper的子类A被继承为类B
	 * 类B使用自己的配置，还是继承A的？
	 * 只有当类B的getAgsHjId()方法返回了和父类不同的值的时候，类B有自己的配置
	 *
	 * example:
	 * public static function getAgsHjIdS()
	 * {
	 * 	return __CLASS__;
	 * }
	 *
	 * agsHjId is actually means a table
	 * TODO: use tableName() to determin?
	 *
	 * @return string id
	 */
	abstract public static function getAgsHjIdS();

	/**
	 * shortcut to static {@link getAgsHjIdS}
	 */
	public function getAgsHjId()
	{
		return call_user_func(array(get_class($this),'getAgsHjIdS'));
	}

	public function __construct($scenario='insert')
	{
		// override this constructor to init helljumper config before every thing
		// use call_user_func() to dynamically call subclass-override
		// DO call parent::__construct() in override
		// 重载构造函数来确保初始化helljumper的配置
		// 用 call_user_func() 来动态地调用子类重载的方法
		// 如果重载构造函数，一定要记得调用 parent::__construct()
		call_user_func(array(get_class($this),'initAgsHjConfigS'),$this->getAgsHjId());

		parent::__construct($scenario);
	}

	/**
	 *
	 * @param string $class
	 * @param string $key
	 * @return bool
	 */
	protected static function hasAgsHjConfigS($class,$key)
	{
		return key_exists($key,self::$_agsHjConfig[$class]);
	}

	/**
	 * shortcut to {@link hasAgsHjConfig}
	 *
	 * @param string $key
	 * @return bool
	 */
	protected function hasAgsHjConfig($key)
	{
		return self::hasAgsHjConfigS($this->getAgsHjId(),$key);
	}

	/**
	 * get configs of a helljumper class
	 * omitting $key to get all config of a class
	 * or provide a key to get part of it
	 * 取得某一个helljumper类的配置
	 * 忽略$key参数取得该类的整个配置数组
	 * 或者提供$key参数来取得配置数组中的一个元素
	 *
	 * @param string $class
	 * @param string $key
	 * @return mixed config array or one element of it
	 */
	protected static function getAgsHjConfigS($class,$key = '')
	{
		call_user_func(array(get_class($this),'initAgsHjConfigS'),self::getAgsHjIdS());

		if ($key)
		{
			if (key_exists($key,self::$_agsHjConfig[$class]))
			{
				return self::$_agsHjConfig[$class][$key];
			}
			else
			{
				return null;
			}
		}
		else
		{
			return self::$_agsHjConfig[$class];
		}
	}

	/**
	 * shortcut to getAgsHjConfigS()
	 *
	 * @param string $key
	 * @return mixed config array or one element of it
	 */
	protected function getAgsHjConfig($key = '')
	{
		return self::getAgsHjConfigS($this->getAgsHjId(),$key);
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
	 * shortcut to setAgsHjConfigS()
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	protected function setAgsHjConfig($key,$value)
	{
		self::setAgsHjConfigS($this->getAgsHjId(),$key,$value);
	}

	/**
	 * override this method to make subclass logics
	 * DO call parent::initAgsHjConfigS($class) in override
	 *
	 * @param string $class
	 */
	public static function initAgsHjConfigS($class)
	{
		// check if config is loaded
		// 检查配置是否已经载入
		if (!isset(self::$_agsHjConfig[$class]))
		{
			// Y::p('helljumper')[$class] exists means this AR is droped outside
			// 如果Y::p('helljumper')[$class]存在，判断这个类运行在外部应用
			if (($clientHelljumperConfig = Y::p('helljumpers')) && isset($clientHelljumperConfig[$class]))
			{
				if (file_exists($file=call_user_func(array($class,'getAgsHjConfigFilePath'))))
				{
					// merge mother-side config and then drop-in-side's
					// 合并mother app側的配置文件和dropin-in側配置，后者覆盖前者
					self::$_agsHjConfig[$class] = array_merge(include($file),$clientHelljumperConfig[$class]);

					// AgsAR metadata
					// TODO what this codes is here?
					if (isset(self::$_agsHjConfig[$class]['agsMetadataColumn']))
					{
						$this->setAgsMetaColumn(self::$_agsHjConfig[$class]['agsMetadataColumn']);
					}
				}
				else
				{
					throw new CException('err:missingConfig:'.$class);
				}
			}
			// else consider it's in mother-app
			// 否则认为正在mother app里
			else
			{
				self::$_agsHjConfig[$class] = array('dataAccessMode' => self::DATA_ACCESS_NATIVE);
			}

			// valid config
			// 检查配置完整性
			switch (self::$_agsHjConfig[$class]['dataAccessMode'])
			{
				case self::DATA_ACCESS_DB:
					if (!self::model()->dbConnection instanceof CDbConnection)
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
	 * 重载以使得子类被import到外部应用的时候，仍能访问数据库
	 *
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

	/**
	 * override to make possible to specify a language file
	 * 重载以使得指定从哪个语言文件读取属性label成为可能
	 *
	 * @param string $attribute
	 */
	public function getAttributeLabel($attribute)
	{
		if (!$this->hasAgsHjConfig('languageCategory'))
		{
			$this->setAgsHjConfig('languageCategory','local');
		}

		$messageKey = $this->getAgsHjId().':'.$attribute.':label';
		$label = Y::t($this->getAgsHjConfig('languageCategory'),$messageKey);

		if ($messageKey == $label)
		{
			$label = Y::t($this->getAgsHjConfig('languageCategory'),$this->getAgsHjId().':'.$attribute);
		}

		if ('none' === $label)
		{
			$label = '';
		}
		return $label;
	}
}