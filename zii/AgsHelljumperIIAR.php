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

	protected $config;
	protected $_db;

	abstract public function getConfigFilePath();

	public function __construct($scenario='insert',$db=null)
	{
		if ($db instanceof CDbConnection)
		{
			$this->_db = $db;
		}
		else
		{
			$helljumperConfig = Y::p('helljumpers');

			// Y::p('helljumper') exists means this AR is droped outside
			if ($helljumperConfig && isset($helljumperConfig[$class = get_class($this)]))
			{
				if (file_exists($this->configFilePath))
				{
					$this->config = include($this->configFilePath);
				}
				else
				{
					throw new CException('err:missingConfig:'.$class);
				}

				$this->config = array_merge($this->config,$helljumperConfig[$class]);

				switch ($this->config['dataAccessMode'])
				{
					case self::DATA_ACCESS_DB:
						if (isset($this->config['db']) && is_array($this->config['db']))
						{
							$this->dbConnection = new CDbConnection;

							foreach ($this->config['db'] as $key=>$value)
							{
								$this->dbConnection->$key = $value;
							}

							$this->dbConnection->active = true;
						}
						else
						{
							throw new CException('err:invalidDbConfig:'.get_class($this));
						}
					break;

					case self::DATA_ACCESS_SOAP:
						throw new CException('err:incomplete');
					break;
				}
			}
		}

		parent::__construct($scenario);
	}

	/**
	 * override to use Shangrila's database connection.
	 * @return CDbConnection the database connection used by active record.
	 */
	public function getDbConnection()
	{
		if ($this->_db instanceof CDbConnection)
		{
			return $this->_db;
		}
		else
		{
			return parent::getDbConnection();
		}
	}
}