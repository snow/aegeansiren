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
	
	protected function renewCookie()
	{
		$cookies=Yii::app()->getRequest()->getCookies();
		$cookie=$cookies->itemAt($this->getStateKeyPrefix());
		if($cookie && !empty($cookie->value) && ($data=Yii::app()->getSecurityManager()->validateData($cookie->value))!==false)
		{
			$data=@unserialize($data);
			if(is_array($data) && isset($data[0],$data[1],$data[2],$data[3]))
			{
				if(is_array($this->identityCookie))
				{
					foreach($this->identityCookie as $name=>$value)
						$cookie->$name=$value;
				}
				$cookie->expire=time()+$data[2];
				$cookies->remove($this->getStateKeyPrefix());
				$cookies->add($cookie->name,$cookie);
			}
		}
	}
}