<?php

/**
 * AgsLoginForm class.
 * AgsLoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'SiteController'.
 */
class AgsLoginForm extends AgsForm
{
	public $account;
	public $password;
	public $rememberMe;
	public $model;

	/**
	 * Declares the validation rules.
	 * The rules state that username and password are required,
	 * and password needs to be authenticated.
	 */
	public function rules()
	{
		return array(
			// username and password are required
			array('account, password', 'required'),
			// password needs to be authenticated
			array('rememberMe','boolean'),
			array('password', 'authenticate'),
		);
	}

	/**
	 * Authenticates the password.
	 * This is the 'authenticate' validator as declared in rules().
	 */
	public function authenticate($attribute,$params)
	{
		if(!$this->hasErrors())  // we only want to authenticate when no input errors
		{
			$this->model = AgsUser::model()->findByAttributes(array('email'=>$this->account));

			if (null === $this->model)
			{
				$this->model = AgsUser::model()->findByAttributes(array('username'=>$this->account));
			}

			if (null === $this->model)
			{
				$this->addError('account',Y::t('ags','error:userNotFound',array('{account}'=>$this->account)));
			}
			elseif ('active' !== $this->model->status)
			{
				$this->addError('account',Y::t('local','error:accountNotActivated')
					.' >> '.AgsHtml::link(Y::t('local','User:resentActivateMail'),
						array('user/resentActivateMail','email'=>$this->model->email),
						array('class'=>'s-q')));
			}
			elseif (!$this->model->validatePassword($this->password))
			{
				$this->addError('password',Y::t('ags','error:wrongPassword'));
			}
		}
	}
}
