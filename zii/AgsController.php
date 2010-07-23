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
		// root
		if (Y::u()->isRoot)
		{
			return;
		}

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
}