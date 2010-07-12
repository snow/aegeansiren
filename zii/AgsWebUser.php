<?php

class AgsWebUser extends CWebUser
{
	public function getIsRoot()
	{
		return 1 == $this->id;
	}

	public function getName()
	{
		if(($name=$this->getState('__name'))!==null)
			return $name;
		else
			return Y::t('local','guest');
	}

	public function addNotification($message,$level)
	{
		$notes = $this->getState('__notification');
		if (!is_array($notes))
		{
			$notes = array();
		}
		$notes[] = array(
			'lv'=>$level,
			'msg'=>$message,
		);
		$this->setState('__notification',$notes);
	}

	public function clearNotifications()
	{
		$this->setState('__notification',null);
	}

	public function getNotifications()
	{
		$notes = array(
			'error'=>array(),
			'note'=>array(),
		);

		if ($this->hasState('__notification'))
		{
			foreach ($this->getState('__notification') as $note)
			{
				$notes[(AgsController::NOTIFICATION_LV_ERROR === $note['lv'])?'s-err':'s-note'][] = $note['msg'];
			}
		}

		return $notes;
	}
}