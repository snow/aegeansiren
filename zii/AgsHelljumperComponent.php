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
abstract class AgsHelljumperComponent extends CComponent
{
	const DATA_ACCESS_NATIVE = 'native';
	const DATA_ACCESS_DB = 'db';
	const DATA_ACCESS_SOAP = 'soap';

	/**
	 *
	 * @var array
	 */
	protected $config;

	/**
	 *
	 * @var string enum('native','db','soap')
	 */
	public $dataAccessMode;
	/**
	 *
	 * @var string
	 */
	public $remoteApiUri;

	/**
	 *
	 * @var CDbConnection
	 */
	protected $dbConnection;

	/**
	 * @return array database config for helljumper to instantiate a CDbConnection instance
	 */
	abstract protected function getConfigFilePath();

	/**
	 * plz do call parent::init() in child classes
	 */
	public function init()
	{
		if (file_exists($this->configFilePath))
		{
			$this->config = include($this->configFilePath);
		}
		else
		{
			throw new CException('err:missingConfig:'.get_class($this));
		}

		// ensure data access
		switch ($this->dataAccessMode)
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

			case self::DATA_ACCESS_NATIVE:
				$this->dbConnection = Y::a()->db;
			break;

			default:
				throw new CException('err:incomplete');
			break;
		}
	}
}