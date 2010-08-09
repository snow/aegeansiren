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
}