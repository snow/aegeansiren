<?php

abstract class AgsHelljumperAR extends AgsAR
{
	const DATA_ACCESS_NATIVE = 'native';
	const DATA_ACCESS_DB = 'db';
	const DATA_ACCESS_SOAP = 'soap';

	public $remoteApiUrl;
	protected static $_dataAccessMethod;
	protected $_db;

	public function init(){}

	abstract public function getConfigFile();

	public function __construct($scenario='insert')
	{
		if($scenario===null) // internally used by populateRecord() and model()
			return;

		$this->setScenario($scenario);
		$this->setIsNewRecord(true);

		$this->init();

		$this->attachBehaviors($this->behaviors());
		$this->afterConstruct();
	}

	public function getNewInstance()
	{
		$class = get_class($this);
		$instance = new $class;
		$instance->dataAccessMethod = $instance->dataAccessMethod;
		return $instance;
	}

	public function getDataAccessMethod()
	{
		if (null===self::$_dataAccessMethod)
		{
			self::$_dataAccessMethod = self::DATA_ACCESS_NATIVE;
		}
		return self::$_dataAccessMethod;
	}

	public function setDataAccessMethod($dataAccessMethod)
	{
		self::$_dataAccessMethod = $dataAccessMethod;
		switch (self::$_dataAccessMethod)
		{
			case self::DATA_ACCESS_DB:
			case self::DATA_ACCESS_NATIVE:
				$this->_attributes=$this->getMetaData()->attributeDefaults;
			break;

			default:
				throw new CException('err:incomplete');
			break;
		}
	}

	/**
	 * override to use Shangrila's database connection.
	 * @return CDbConnection the database connection used by active record.
	 */
	public function getDbConnection()
	{
		switch ($this->dataAccessMethod)
		{
			case self::DATA_ACCESS_DB:
				if (null===$this->_db)
				{
					if (file_exists($this->configFile))
					{
						$config = include($this->configFile);

						if (isset($config['components']['db'])
							&& is_array($config['components']['db'])
							&& $config['components']['db']['connectionString'])
						{
							$this->_db = new CDbConnection;

							foreach ($config['components']['db'] as $key=>$value)
							{
								$this->_db->$key = $value;
							}

							$this->_db->active = true;
						}
						else
						{
							throw new CException('err:invalidDbConfig');
						}
					}
					else
					{
						throw new CException('err:missingConfigForSglWebUser');
					}
				}
				return $this->_db;
			break;

			case self::DATA_ACCESS_NATIVE:
				return Y::a()->db;
			break;

			default:
				throw new CException('err:dbConnectionNotSupportedInMode:'.$this->dataAccessMethod);
			break;
		}
	}
}