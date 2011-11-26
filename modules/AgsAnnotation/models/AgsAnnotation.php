<?php

/**
 * This is the model class for table "AgsAnnotation".
 */
class AgsAnnotation extends AgsAR
{
	/**
	 * The followings are the available columns in table 'AgsAnnotation':
	 * @var integer $id
	 * @var string $subtype
	 * @var string $desc
	 * @var integer $ownerId
	 * @var integer $containerId
	 * @var string $containerClass
	 * @var integer $accessId
	 * @var integer $created
	 * @var integer $updated
	 * @var string $status
	 * @var integer $enabled
	 */

	/**
	 * Returns the static model of the specified AR class.
	 * @return AgsAnnotation the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'AgsAnnotation';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('accessId', 'required'),
			array('accessId', 'numerical', 'integerOnly'=>true),
			array('desc', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, subtype, desc, ownerId, containerId, containerClass, accessId, created, updated, status, enabled', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);

		$criteria->compare('subtype',$this->subtype,true);

		$criteria->compare('desc',$this->desc,true);

		$criteria->compare('ownerId',$this->ownerId);

		$criteria->compare('containerId',$this->containerId);

		$criteria->compare('containerClass',$this->containerClass,true);

		$criteria->compare('accessId',$this->accessId);

		$criteria->compare('created',$this->created);

		$criteria->compare('updated',$this->updated);

		$criteria->compare('status',$this->status,true);

		$criteria->compare('enabled',$this->enabled);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}