<?php

class DefaultController extends Controller
{
	/**
	 * @var CActiveRecord the currently loaded data model instance.
	 */
	private $_model;

	/**
     * @return array action filters
     */
    public function filters()
    {
        return array(
        );
    }

	/**
	 * register
	 */
	public function actionSignup()
	{
		/*$model=new AgsUser;
		if(isset($_POST['AgsUser']))
		{
			$model->attributes=$_POST['AgsUser'];
			if($model->save())
			{
                $this->sendActivateMail($model);
                $this->pageTitle=Y::t('local','User:reg:done');
                $this->render(
                    array(
                        'main'=>array(
                            'view'=>'/system/message',
                            'title'=>Y::t('local','User:reg:done'),
                            'message'=>Y::t('local','User:reg:done:more'),
                            'class'=>'s-scs',
                        ),
                    ),
                    array(
                        'docId'=>'register',
                        'docClass'=>'w500',
                        'layout'=>'m0',
                    )
                );
                return;
			}
		}

		$this->render(
            array(
                'main'=>array(
                    'view'=>'register',
                    'model'=>$model,
                ),
                'header'=>array(
                    'view'=>'/system/header',
                    'search'=>false,
                    'menu'=>false,
                ),
            ),
            array(
                'docId'=>'register',
                'docClass'=>'w500',
                'layout'=>'m0'
            )
		);*/
	}

	protected function sendActivateMail(User $model)
	{
		/*Yii::app()->mta->send(array(
            'to'=>array('addr'=>$model->email,'name'=>$model->name),
            'subject'=>Y::t('local','User:activateMail:subject',array('{name}'=>$model->name)),
            'body'=>Y::t('local','User:activateMail:body',array(
                '{name}'=>$model->name,
                '{link}'=>$this->createAbsoluteUrl('user/activate',array(
                    'id'=>$model->id,
                    'c'=>$model->activateCode))))
        ));*/
	}

    public function actionLogin()
    {
        $form=new LoginForm;
        // collect user input data
        if(isset($_POST['LoginForm']))
        {
            $form->attributes=$_POST['LoginForm'];
            // validate user input and redirect to previous page if valid
            if($form->validate())
            {
            	$this->login($form->model,$form->rememberMe);
                $this->redirect(Y::u()->returnUrl);
            }
        }
        // display the login form
        $this->pageTitle = Y::t('ags','login');
        /*$this->render(
            array(
                'main'=>array(
                    'view'=>'login',
                    'form'=>$form,
                ),
            ),
            array(
                'docId'=>'login',
                'docClass'=>'w500',
                'layout'=>'m0'
            )
        );*/
    }

    protected function login(User $user,$remember = false)
    {
        $identity = new AgsUserIdentity;
        $identity->id = $user->id;
        $identity->username = $user->name;
        $identity->model = $user;

        Y::u()->login($identity,$remember?3600*24*30:0);
    }

    public function actionLogout()
    {
        Y::u()->logout();
        $this->redirect('/');
    }

    public function actionActivate()
    {
        /*$model = $this->loadModel();

        if ($model->activateCode === $_GET['c'])
        {
            $model->scenario = 'activate';
            $model->active = true;
            if ($model->save())
            {
                if (Y::u()->isGuest)
                {
                    $this->login($model);
                }
                $this->redirect('/');
            }
        }
        throw new CHttpException(404);*/
    }

    public function actionResentActivateMail()
    {
        /*$this->pageTitle = Y::t('local','User:resentActivateMail');

    	if (isset($_POST['email']))
        {
            $model = User::model()->findByAttributes(array('email'=>$_POST['email']));

            if ($model instanceof User)
            {
                $this->sendActivateMail($model);

                $this->render(
                    array(
                        'main'=>array(
                            'view'=>'/system/message',
                            'message'=>Y::t('local','User:activateMail:sent'),
                            'class'=>'s-scs',
                        ),
                    ),
                    array(
                        'docId'=>'resentActivateMail',
                        'docClass'=>'w500',
                        'layout'=>'m0'
                    )
                );
                return;
            }
            else
            {
                $message = Y::t('ags','error:userNotFound',array('{account}'=>$_POST['email']));
            }
        }

        $this->render(
            array(
                'main'=>array(
                    'view'=>'resentActivateMail',
                    'message'=>$message,
                    'email'=>$_GET['email'],
                ),
            ),
            array(
                'docId'=>'resentActivateMail',
                'docClass'=>'w500',
                'layout'=>'m0'
            )
        );*/
    }

    public function actionRecoverPassword()
    {
        /*if ($_GET['id'] && $_GET['c'])
        {
            $model = $this->loadModel($_GET['id']);
            if ($model && ($_GET['c'] == md5($model->salt.$model->passwordHash)))
            {
                if (isset($_POST['User']))
                {
                    $model->scenario = 'resetPassword';
                    $model->attributes = $_POST['User'];
                    if ($model->save())
                    {
                        $this->pageTitle = Y::t('ags','resetPassword:done');
                        $this->render(
                            array(
                                'main'=>array(
                                    'view'=>'/system/message',
                                    'title'=>Y::t('ags','happy').Y::t('ags','resetPassword:done'),
                                    'message'=>AgsHtml::link(Y::t('ags','login'),array('user/login')),
                                    'class'=>'s-scs',
                                ),
                            ),
                            array(
                                'docId'=>'resetPassword',
                                'docClass'=>'w500',
                                'layout'=>'m0'
                            )
                        );
                        return;
                    }
                }
                $this->pageTitle = Y::t('ags','resetPassword');
                $this->render(
                    array(
                        'main'=>array(
                            'view'=>'resetPassword',
                            'model'=>$model,
                        ),
                    ),
                    array(
                        'docId'=>'resetPassword',
                        'docClass'=>'w500',
                        'layout'=>'m0'
                    )
                );
            }
        }
        elseif (isset($_POST['email']))
        {
            $model = User::model()->findByAttributes(array('email'=>$_POST['email']));

            if ($model instanceof User)
            {
                Yii::app()->mta->send(array(
                    'to'=>array('addr'=>$model->email,'name'=>$model->name),
                    'subject'=>Y::t('ags','recoverPassword:mail:subject',array('{name}'=>$model->name)),
                    'body'=>Y::t('ags','recoverPassword:mail:body',array(
                        '{name}'=>$model->name,
                        '{link}'=>$this->createAbsoluteUrl('user/recoverPassword',array(
                            'id'=>$model->id,
                            'c'=>md5($model->salt.$model->passwordHash)))))
                ));

                $this->pageTitle = Y::t('ags','recoverPassword:mail:sent');
                $this->render(
                    array(
                        'main'=>array(
                            'view'=>'/system/message',
                            'title'=>Y::t('ags','recoverPassword:mail:sent'),
                            'message'=>Y::t('ags','recoverPassword:mail:sent:more'),
                            'class'=>'succeed',
                        ),
                    ),
                    array(
                        'docId'=>'recoverPassword',
                        'docClass'=>'w500',
                        'layout'=>'m0'
                    )
                );
                return;
            }
            else
            {
                $this->pageTitle=Y::t('ags','recoverPassword');
                $this->render(
                    array(
                        'main'=>array(
                            'view'=>'recoverPassword',
                            'message'=>Y::t('ags','error:userNotFound',array('{account}'=>$_POST['email'])),
                            'email'=>$_GET['email'],
                        ),
                    ),
                    array(
                        'docId'=>'recoverPassword',
                        'docClass'=>'w500',
                        'layout'=>'m0'
                    )
                );
            }
        }
        else
        {
            $this->pageTitle=Y::t('ags','recoverPassword');
            $this->render(
                array(
                    'main'=>array(
                        'view'=>'recoverPassword',
                        'email'=>$_GET['email'],
                    ),
                ),
                array(
                    'docId'=>'recoverPassword',
                    'docClass'=>'w500',
                    'layout'=>'m0'
                )
            );
        }*/
    }

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionUpdate()
	{
		$model=$this->loadModel();
		if(isset($_POST['AgsUser']))
		{
			$model->attributes=$_POST['AgsUser'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 */
	public function actionDelete()
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$this->loadModel()->delete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_POST['ajax']))
				$this->redirect(array('index'));
		}
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$dataProvider=new CActiveDataProvider('User', array(
			'pagination'=>array(
				'pageSize'=>self::PAGE_SIZE,
			),
		));

		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$dataProvider=new CActiveDataProvider('User', array(
			'pagination'=>array(
				'pageSize'=>self::PAGE_SIZE,
			),
		));

		$this->render('admin',array(
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 */
	public function loadModel()
	{
		if($this->_model===null)
		{
			if(isset($_GET['id']))
				$this->_model=AgsUser::model()->findbyPk($_GET['id']);
			if($this->_model===null)
				throw new CHttpException(404,'The requested page does not exist.');
		}
		return $this->_model;
	}
}