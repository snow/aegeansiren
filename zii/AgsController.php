<?php

abstract class AgsController extends CController
{
	const NOTIFICATION_LV_NOTE = 1;
	const NOTIFICATION_LV_ERROR = 2;

	protected function addUserNotification($message,$level=self::NOTIFICATION_LV_NOTE)
	{
		Y::u()->addNotification($message,$level);
	}

	protected function accessControl($controlString = '')
	{
		$accessRule = new AgsAccessRule($controlString);
		if (!$accessRule->execRule())
		{
			throw new CHttpException(403);
		}
	}

	public function render($view,$data=null,$return=false,$processOutput=false)
	{
		$output=$this->renderPartial($view,$data,true);
		if(($layoutFile=$this->getLayoutFile($this->layout))!==false)
		{
			$notifications = Y::u()->getNotifications();
			Y::u()->clearNotifications();

			$output=$this->renderFile($layoutFile,array(
				'content'=>$output,
				'notifications'=>$notifications,
				'docId'=>$this->id.ucfirst($this->action->id),
			),true);
		}

		if ($processOutput)
		{
			$output=$this->processOutput($output);
		}

		if($return)
			return $output;
		else
			echo $output;
	}

	protected function ajaxResponse($success = true,$data,$terminate = true)
	{
		echo json_encode(array_merge(array(
			'success'=>$success,
		),$data));

		if ($terminate)
		{
			Y::a()->end();
		}
	}

	public function ajaxSuccess($data,$terminate = true)
	{
		$this->ajaxResponse(true,$data,$terminate);
	}

	public function ajaxError($message,$terminate = true)
	{
		$this->ajaxResponse(false,array('message'=>$message),$terminate);
	}
}