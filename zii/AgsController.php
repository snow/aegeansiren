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
				'sidebar'=>isset($data['sidebar'])?$data['sidebar']:false,
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
		$response = array('success'=>$success);

		if (is_array($data))
		{
			$response = array_merge($response,$data);
		}

		echo json_encode($response);

		if ($terminate)
		{
			Y::a()->end();
		}
	}

	public function ajaxSuccess($data = null,$terminate = true)
	{
		$this->ajaxResponse(true,$data,$terminate);
	}

	public function ajaxError($message,$terminate = true)
	{
		$this->ajaxResponse(false,array('message'=>$message),$terminate);
	}
}