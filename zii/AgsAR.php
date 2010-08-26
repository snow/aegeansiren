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
	const AGS_METADATA_KEY_MODE_AUTO = 'auto';

	public function init()
	{
		parent::init();

		if ($this->hasAttribute($this->getAgsMetaColumn()))
		{
			$this->_agsMetadata = $this->getAgsMetaDefaults();

			if (self::AGS_METADATA_KEY_MODE_AUTO === $this->_agsMetadata)
			{
				$this->_agsMetadata = array();
			}
		}
	}

	protected function afterFind()
	{
		parent::afterFind();

		if (is_array($record = json_decode($this->getAttribute($this->getAgsMetaColumn()),true)))
		{
			$this->_agsMetadata = array_merge($this->_agsMetadata,$record);
		}
	}

	protected function getAgsMetaColumn()
	{
		return 'metaSerial';
	}

	public function __get($name)
	{
		$getter = 'get'.$name;

		if (method_exists($this,$getter))
		{
			return $this->$getter();
		}
		elseif ($this->hasAgsMetadata($name))
		{
			return $this->getAgsMetadata($name);
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
		elseif ($this->hasAgsMetadata($name))
		{
			$this->setAgsMetadata($name,$value);
		}
		else
		{
			parent::__set($name,$value);
		}
	}

	public function getAgsMetadata($key = null)
	{
		if (null === $key)
		{
			return $this->_agsMetadata;
		}
		elseif (key_exists($key,$this->_agsMetadata))
		{
			return $this->_agsMetadata[$key];
		}
		else
		{
			return null;
		}
	}

	public function setAgsMetadata($key,$value)
	{
		if ((self::AGS_METADATA_KEY_MODE_AUTO === $this->getAgsMetaDefaults()) || $this->hasAgsMetadata($key))
		{
			$this->_agsMetadata[$key] = $value;
		}
		elseif (is_array($key))
		{
			$this->_agsMetadata = $key;
		}
		else
		{
			throw new CException('class '.get_class($this).' dosen\'t has metadata '.$key);
		}
	}

	public function hasAgsMetadata($key)
	{
		if (self::AGS_METADATA_KEY_MODE_AUTO === $this->getAgsMetaDefaults())
		{
			return (is_array($this->_agsMetadata) && key_exists($key,$this->_agsMetadata));
		}
		else
		{
			return key_exists($key,$this->getAgsMetaDefaults());
		}
	}

	public function getAgsMetaDefaults()
	{
		return self::AGS_METADATA_KEY_MODE_AUTO;
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
		if ($this->hasAttribute($this->getAgsMetaColumn()) && is_array($this->_agsMetadata))
		{
			$this->setAttribute($this->getAgsMetaColumn(),json_encode($this->_agsMetadata));
		}

		return parent::beforeSave();
	}
}