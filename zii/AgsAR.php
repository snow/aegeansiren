<?php
/**
 * AegeanSiren ActiveRecord
 *
 * New: metadata support
 * New: auto timestamp
 * Chg: prefer to getter or setter method than to access property directly in __get() and __set()
 *
 * @author snow.hellsing@gmail.com
 *
 */
abstract class AgsAR extends CActiveRecord
{
	private $_agsMetadata;
	private static $_agsMetadataColumnName;

	public function __construct($scenario = 'insert')
	{
		if (null === self::$_agsMetadataColumnName)
		{
			if (($config = Y::p(get_class($this))) && is_array($config) && isset($config['agsMetadataColumn']))
			{
				self::$_agsMetadataColumnName = $config['agsMetadataColumn'];
			}
			else
			{
				self::$_agsMetadataColumnName = 'metaSerial';
			}
		}

		parent::__construct($scenario);
	}

	public function __get($name)
	{
		$getter = 'get'.$name;

		if (method_exists($this,$getter))
		{
			return $this->$getter();
		}
		elseif (in_array($name,$this->getAgsMetaKeys()))
		{
			return $this->_agsMetadata[$name];
		}
		else
		{
			return parent::__get($name);
		}
	}

	public function __set($name,$value)
	{
		$setter = 'set'.$name;

		if (method_exists($this,$setter))
		{
			$this->$setter($value);
		}
		elseif (in_array($name,$this->getAgsMetaKeys()))
		{
			$this->_agsMetadata[$name] = $value;
		}
		else
		{
			parent::__set($name,$value);
		}
	}

	public function getAgsMetadata()
	{
		if (null === $this->_agsMetadata)
		{
			if ($this->hasAttribute(self::$_agsMetadataColumnName))
			{
				$this->_agsMetadata = json_decode($this->getAttribute(self::$_agsMetadataColumnName),true);
			}

			// for when metadata is empty
			if (!is_array($this->_agsMetadata))
			{
				$this->_agsMetadata = array();
			}
		}

		return $this->_agsMetadata;
	}

	public function setAgsMetadata($metadata)
	{
		if (is_array($metadata))
		{
			$this->_agsMetadata = $metadata;
		}
	}

	public function getAgsMetaKeys()
	{
		return array_keys($this->getAgsMetadata());
	}

	public function getAttributeLabel($attribute)
	{
		$messageKey = get_class($this).':'.$attribute.':label';
		$label = Y::t('local',$messageKey);

		if ($messageKey == $label)
		{
			$label = Y::t('local',get_class($this).':'.$attribute);
		}

		if ('none' === $label)
		{
			$label = '';
		}
		return $label;
	}

	protected function beforeValidate()
	{
		if (in_array($this->scenario,array('insert','update')))
		{
			$time = time();
			if ( $this->hasAttribute('created') && $this->isNewRecord)
				$this->created = $time;

			if ( $this->hasAttribute('updated') )
				$this->updated = $time;
		}

		return parent::beforeValidate();
	}

	protected function beforeSave()
	{
		if ($this->hasAttribute(self::$_agsMetadataColumnName) && is_array($this->_agsMetadata))
		{
			$this->setAttribute(self::$_agsMetadataColumnName,json_encode($this->_agsMetadata));
		}

		return parent::beforeSave();
	}
}