<?php

class AgsController extends CController
{
	const NOTIFICATION_LV_NOTE = 1;
	const NOTIFICATION_LV_ERROR = 2;

	protected function addUserNotification($message,$level=self::NOTIFICATION_LV_NOTE)
	{
		;
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
}