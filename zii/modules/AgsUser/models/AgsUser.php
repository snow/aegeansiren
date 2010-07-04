<?php

/**
 * This is the model class for table "AgsUser".
 */
class AgsUser extends AgsAR
{
	/**
	 * The followings are the available columns in table 'AgsUser':
	 * @var integer $id
	 * @var string $email
	 * @var string $username
	 * @var string $name
	 * @var string $passwordHash
	 * @var string $salt
	 * @var string $subtype
	 * @var integer $accessId
	 * @var integer $created
	 * @var integer $updated
	 * @var string $status
	 * @var integer $enabled
	 */

	/**
	 * Returns the static model of the specified AR class.
	 * @return AgsUser the static model class
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
		return 'AgsUser';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('email, username, name', 'required'),
			array('email', 'length', 'max'=>40),
			array('username', 'length', 'max'=>12),
			array('name', 'AgsStringValidator', 'max'=>10),

			/* email */
			array('email', 'unique',
                'message'=>Y::t('local','User:email').' '.Y::t('ags','error:uniqueConflic')),
			array('email', 'email'),

			/* set password on register */
			array('password','required','on'=>'insert'),
			array('password','length','on'=>'insert','min'=>6),

			/* change passwod on recover password or update account */
            array('newPassword','length','on'=>'update','min'=>6),
            array('originPassword','changePasswordValidator','on'=>'update'),
            array('newEmail','changeEmailValidator','on'=>'update'),

			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, email, username, name, subtype, accessId, created, updated, status, enabled', 'safe', 'on'=>'search'),
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

		$criteria->compare('email',$this->email,true);

		$criteria->compare('username',$this->username,true);

		$criteria->compare('name',$this->name,true);

		$criteria->compare('subtype',$this->subtype,true);

		$criteria->compare('accessId',$this->accessId);

		$criteria->compare('created',$this->created);

		$criteria->compare('updated',$this->updated);

		$criteria->compare('status',$this->status,true);

		$criteria->compare('enabled',$this->enabled);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}

	/**
     * check if sumitted origin password is valid
     * if no new password or new emial is set,will skip check
     *
     * @param $attribute originPassword
     * @param $params
     */
    public function changePasswordValidator($attribute,$params)
    {
        if (($this->newPassword || $this->newEmail) && !$this->hasErrors())
        {
            if (!$this->validatePassword($this->$attribute))
            {
                $this->addError($attribute,Y::t('ags','error:wrongPassword'));
            }
        }
    }

    /**
     * compare given string and the hashed password in system
     * @param string $password
     */
    public function validatePassword($password)
    {
        return ($this->generatePassword($password) === $this->passwordHash);
    }

    /**
     * hash given string with salt
     * @param string $password
     */
    protected function generatePassword($password)
    {
        if (null === $this->salt)
        {
            $this->salt = md5(time());
        }
        return md5($password.$this->salt);
    }

    /**
     * check if given email exists in system
     * @param $attribute newEmail
     * @param $params
     */
    public function changeEmailValidator($attribute,$params)
    {
        if ($this->newEmail && !$this->hasErrors())
        {
            if (self::model()->exists('email=:email',array(':email'=>$this->newEmail)))
            {
                $this->addError('newEmail',Y::t('ags','error:uniqueConflic',array('{value}'=>$this->newEmail)));
            }
            else
            {
                $this->email = $this->newEmail;
            }
        }
    }

    protected function afterValidate()
    {
        parent::afterValidate();
        /*
         * hash password string
         */
        if ('insert' === $this->scenario)
        {
            $this->passwordHash = $this->generatePassword($this->password);
        }
        elseif (('update' === $this->scenario) && !empty($this->newPassword))
        {
            $this->passwordHash = $this->generatePassword($this->newPassword);
        }
    }

	public function delete()
	{
		if ($this->isNewRecord)
		{
			throw new CException(Y::t('ags','err:deletingNewRecord'));
		}
		$this->enabled = false;
		$this->save(false);
	}
}