<?php

abstract class AgsController extends CController
{
	const NOTE_LV_NOTE = 1;
	const NOTE_LV_ERROR = 2;

	public function addUserNote($message,$level=self::NOTE_LV_NOTE)
	{
		Y::u()->addNote($message,$level);
	}

	protected function accessControl($controlString = '')
	{
		$accessRule = new AgsAccessRule($controlString);
		if (!$accessRule->exec())
		{
			throw new CHttpException(403);
		}
	}

	public function render($view,$data=null,$return=false,$processOutput=true)
	{
		$output=$this->renderPartial($view,$data,true);
		if(($layoutFile=$this->getLayoutFile($this->layout))!==false)
		{
			$output=$this->renderFile($layoutFile,array(
				'content'=>$output,
				'sidebar'=>isset($data['sidebar'])?$data['sidebar']:false,
				'userNotes'=>Y::u()->getNotes(),
				'docId'=>$this->id.ucfirst($this->action->id),
			),true);

			Y::u()->clearNotes();
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

	protected function ajaxResponse($succeed = true,$data,$terminate = true)
	{
		$response = array('succeed'=>$succeed);

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

	public function ajaxSucceed($data = null,$terminate = true)
	{
		$this->ajaxResponse(true,$data,$terminate);
	}

	public function ajaxFailed($message,$terminate = true)
	{
		$this->ajaxResponse(false,array('message'=>$message),$terminate);
	}

	public function getAutoRedirectUri($defaultUri = null,$deadLoopUri = null)
	{
		if (!$defaultUri) $defaultUri = '/';
		if (is_array($defaultUri))
		{
			$defaultUri = CHtml::normalizeUrl($defaultUri);
		}

		if (Y::r()->urlReferrer)
		{
			if (null === $deadLoopUri)
			{
				$deadLoopUri = $this->createAbsoluteUrl($this->id.'/'.$this->action->id);
			}

			if (false !== stripos(Y::r()->urlReferrer,$deadLoopUri))
			{
				$redirectUri = $defaultUri;
			}
			else
			{
				$redirectUri = Y::r()->urlReferrer;
			}
		}
		else
		{
			$redirectUri = $defaultUri;
		}

		return $redirectUri;
	}

	public function autoRedirect($defaultUri = null,$deadLoopUri = null,$terminate = null)
	{
		$this->redirect($this->getAutoRedirectUri($defaultUri,$deadLoopUri),$terminate);
	}
}