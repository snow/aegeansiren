<?php

class AgsUserModule extends CWebModule
{
	public $requireActivate = true;
	public $defaultAccessLv = Y::ACCESS_SIGNEDIN_USERS;
	public $subtypesOnlyActivateByAdmin = array('admin');

	public function init()
	{
		// this method is called when the module is being created
		// you may place code here to customize the module or the application

		// import the module-level models and components
		$this->setImport(array(
			'AgsUser.models.*',
			'AgsUser.components.*',
		));
	}

	public function beforeControllerAction($controller, $action)
	{
		if(parent::beforeControllerAction($controller, $action))
		{
			// this method is called before any module controller action is performed
			// you may place customized code here
			if (isset($controller->requireActivate))
			{
				$controller->requireActivate = $this->requireActivate;
				$controller->defaultAccessLv = $this->defaultAccessLv;
				$controler->subtypesOnlyActivateByAdmin = $this->subtypesOnlyActivateByAdmin;
			}

			return true;
		}
		else
			return false;
	}
}
