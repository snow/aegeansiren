<?php
/**
 * AegeanSiren ActiveRecord
 * a set of enhancement of ActiveRecord
 * ActiveRecord的一系列增强
 *
 * New: metadata support
 * New: auto timestamp
 * Chg: prefer to getter or setter method than to access property directly in __get() and __set()
 *
 * 增加metadata支持
 * 自动对created和updated字段加timestamp
 *
 * @author snow@firebloom.cc
 *
 */
abstract class AgsAR extends CActiveRecord
{
	private $_agsMetadata;
	const AGS_METADATA_KEY_MODE_AUTO = 'auto';

	public function init()
	{
		parent::init();

		// init internal metadata array if the meta column do exist
		if ($this->hasAttribute($this->getAgsMetaColumn()))
		{
			$this->_agsMetadata = $this->getAgsMetaDefaults();

			if (self::AGS_METADATA_KEY_MODE_AUTO === $this->_agsMetadata)
			{
				$this->_agsMetadata = array();
			}
			// Q: why not if...else...?
			// A: to avoid call $this->getAgsMetaDefaults() twice
			// Q: 为什么不 if...else...?
			// A: 为了避免两次调用 $this->getAgsMetaDefaults()
		}
	}

	protected function afterFind()
	{
		parent::afterFind();

		// load metadata from database
		if (is_array($record = json_decode($this->getAttribute($this->getAgsMetaColumn()),true)))
		{
			$this->_agsMetadata = array_merge($this->_agsMetadata,$record);
		}
	}

	/**
	 * subclass could override this method returnning a different column to store matadata
	 * 子类可以通过重载这个方法，返回其它行来提供metadata
	 */
	protected function getAgsMetaColumn()
	{
		return 'metaSerial';
	}

	/**
	 * override to make getter method has priority and check metadata
	 * 重载以使得getter方法有最高优先级并且会检查metadata
	 *
	 * @param string $name
	 */
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

	/**
	 * override to make setter method has priority and check metadata
	 * 重载以使得setter方法有最高优先级并且会检查metadata
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name,$value)
	{
		$setter = 'set'.$name;

		if (method_exists($this,$setter))
		{
			$this->$setter($value);
		}
		// only use magic set on existed metadata
		// call setAgsMetadata() to manually add metadata
		// 只通过魔术方法给已经存在的metadata赋值
		// 调用实例的 setAgsMetadata() 方法来手动添加新的metadata
		elseif ($this->hasAgsMetadata($name))
		{
			$this->setAgsMetadata($name,$value);
		}
		else
		{
			parent::__set($name,$value);
		}
	}

	/**
	 * subclass could override this method
	 * returning a key=>value array as meta defaults
	 * and $this->hasAgsMetadata($key) will depend on if a key exists in this array
	 *
	 * 子类可重载此方法
	 * 返回一个关联数组用作metadata默认值
	 * 并且这个数据的键将作为$this->hasAgsMetadata($key)的判断依据
	 */
	public function getAgsMetaDefaults()
	{
		return self::AGS_METADATA_KEY_MODE_AUTO;
	}

	/**
	 *
	 * @param string $key
	 */
	public function hasAgsMetadata($key)
	{
		// on auto mode,check current internal metadata array
		if (self::AGS_METADATA_KEY_MODE_AUTO === $this->getAgsMetaDefaults())
		{
			return (is_array($this->_agsMetadata) && key_exists($key,$this->_agsMetadata));
		}
		else
		{
			return key_exists($key,$this->getAgsMetaDefaults());
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

	/**
	 * override to provide attr label by language system
	 *
	 * @param string $attribute
	 */
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
		// auto timestamp
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