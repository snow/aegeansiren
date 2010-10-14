<?php
/**
 * AegeanSiren WebUser
 * enhancements of CWebUser
 * CWebUser的一系列增强
 *
 * New: save notification messages in user session
 * New: across application cookie support in same domain
 * Chg: use language file to provide guest name
 *
 *
 *
 * @author snow@firebloom.cc
 *
 */
abstract class AgsWebUser extends CWebUser
{
	const SITE_MESSAGE_NOTE = 'note';
	const SITE_MSG_ERR = 'error';

	/**
	 * check language file for guest name
	 * 检索语言文件中的未登录用户称谓
	 */
	public function getName()
	{
		if(($name=$this->getState('__name'))!==null)
			return $name;
		else
			return Y::t('local','guest');
	}

	/**
	 * add notification message to user session
	 * 添加一条消息到用户session
	 *
	 * @param string the message
	 * @param string message type,note or error
	 */
	public function addNote($message,$level)
	{
		$notes = $this->getState('ags-userNotes');
		if (!is_array($notes))
		{
			$notes = array();
		}
		$notes[] = array(
			'lv'=>$level,
			'msg'=>$message,
		);
		$this->setState('ags-userNotes',$notes);
	}

	/**
	 * clear all stored message
	 * 清理所有已储存的消息
	 */
	public function clearNotes()
	{
		$this->setState('ags-userNotes',null);
	}

	/**
	 * get all stored notes
	 * 取得所有消息
	 */
	public function getNotes()
	{
		$notes = array(
			's-err'=>array(),
			's-note'=>array(),
		);

		if ($this->hasState('ags-userNotes'))
		{
			foreach ($this->getState('ags-userNotes') as $note)
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
		else
		{
			// logout when cookie expired
			$this->logout();
		}
	}
}