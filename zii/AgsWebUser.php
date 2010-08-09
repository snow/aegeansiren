<?php

abstract class AgsWebUser extends CWebUser
{
	public function getName()
	{
		if(($name=$this->getState('__name'))!==null)
			return $name;
		else
			return Y::t('local','guest');
	}

	public function addNote($message,$level)
	{
		$notes = $this->getState('__notes');
		if (!is_array($notes))
		{
			$notes = array();
		}
		$notes[] = array(
			'lv'=>$level,
			'msg'=>$message,
		);
		$this->setState('__notes',$notes);
	}

	public function clearNotes()
	{
		$this->setState('__notes',null);
	}

	public function getNotes()
	{
		$notes = array(
			'error'=>array(),
			'note'=>array(),
		);

		if ($this->hasState('__notes'))
		{
			foreach ($this->getState('__notes') as $note)
			{
				$notes[(AgsController::NOTE_LV_ERROR === $note['lv'])?'s-err':'s-note'][] = $note['msg'];
			}
		}

		return $notes;
	}

	protected function restoreFromCookie()
	{
		$app=Yii::app();
		$cookie=$app->getRequest()->getCookies()->itemAt($this->getStateKeyPrefix());
		if($cookie && !empty($cookie->value) && ($data=$app->getSecurityManager()->validateData($cookie->value))!==false)
		{
			$data=@unserialize($data);
			if(is_array($data) && isset($data[0],$data[1],$data[2],$data[3]))
			{
				list($id,$name,$duration,$states)=$data;
				if($this->beforeLogin($id,$states,true))
				{
					$this->changeIdentity($id,$name,$states);
					//
					// override to add process on $this->indentityCookie
					//
					if(is_array($this->identityCookie))
					{
						foreach($this->identityCookie as $name=>$value)
							$cookie->$name=$value;
					}
					if($this->autoRenewCookie)
					{
						$cookie->expire=time()+$duration;
						$app->getRequest()->getCookies()->add($cookie->name,$cookie);
					}
					$this->afterLogin(true);
				}
			}
		}
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
				//
				// override to add process on $this->indentityCookie
				//
				if(is_array($this->identityCookie))
				{
					foreach($this->identityCookie as $name=>$value)
						$cookie->$name=$value;
				}
				$cookie->expire=time()+$data[2];
				$cookies->add($cookie->name,$cookie);
			}
		}
	}
}