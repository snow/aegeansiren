<?php
/**
 * AegeanSiren Controller
 * a set of enhancement of CController
 * CController的一系列增强
 *
 * New: in-action access control
 * New: general ajax response
 * New: auto redirect
 *
 * action内定义的访问控制
 * 基础ajax反馈
 * 自动跳转
 *
 * @author snow
 *
 */
abstract class AgsController extends CController
{
	const NOTE_LV_NOTE = 1;
	const NOTE_LV_ERROR = 2;

	/**
	 * @deprecated call Y::u()->addNote() directly
	 * TODO: remove this method?
	 *
	 * @param string the message
	 * @param NOTE_LV_NOTE | NOTE_LV_ERROR
	 */
	public function addUserNote($message,$level=self::NOTE_LV_NOTE)
	{
		Y::u()->addNote($message,$level);
	}

	/**
	 * in-action access control
	 * sub class could override this method
	 *
	 * action内的访问控制
	 * 子类可以重载此方法来提供更多的出错处理
	 *
	 * try{
	 * 	parent::accessControl($controlString);
	 * }
	 * catch(CHttpException)
	 * {
	 * 	//put business logic here
	 * }
	 *
	 * @param string
	 * @see AgsAccessRule
	 */
	protected function accessControl($controlString = '')
	{
		$accessRule = new AgsAccessRule($controlString);
		if (!$accessRule->exec())
		{
			throw new CHttpException(403);
		}
	}

	/**
	 * Renders a view with a layout.
	 *
	 * @deprecated this method still under develop
	 * @deprecated 该方法尚未成熟，随时可能更改
	 *
	 * override to support sidebar and user notifications
	 * and generate id of body tag with format controllerAction
	 *
	 * 重载以令开发者可以灌入sidebar的内容和用户提示
	 * 并且以controllerAction的格式生成body标签的id
	 *
	 * @param string name of the view to be rendered. See {@link getViewFile} for details
	 * about how the view script is resolved.
	 * @param array data to be extracted into PHP variables and made available to the view script
	 * @param boolean whether the rendering result should be returned instead of being displayed to end users.
	 * @return string the rendering result. Null if the rendering result is not required.
	 * @see renderPartial
	 * @see getLayoutFile
	 */
	public function render($view,$data=null,$return=false)
	{
		$output=$this->renderPartial($view,$data,true);
		if(($layoutFile=$this->getLayoutFile($this->layout))!==false)
		{
			$output=$this->processOutput($this->renderFile($layoutFile,array(
				'content'=>$output,
				'sidebar'=>isset($data['sidebar'])?$data['sidebar']:false,
				'userNotes'=>Y::u()->getNotes(),
				'docId'=>$this->id.ucfirst($this->action->id),
			),true));

			Y::u()->clearNotes();
		}

		if($return)
			return $output;
		else
			echo $output;
	}

	/**
	 * general ajax response
	 * 基础ajax返回
	 *
	 * {
	 * 	succeed: true/false,
	 *  other data...
	 * }
	 *
	 * @param bool is the ajax call succeed/ajax调用是否成功
	 * @param array will show in json/将会以json格式返回的数据
	 * @param bool whether to terminate after response/返回之后是否立刻退出程序
	 */
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
			exit;
		}
	}

	/**
	 * shortcut of {@link ajaxResponse}
	 * ajaxResponse方法的快捷方式
	 *
	 * {
	 * 	succeed: true,
	 *  other data...
	 * }
	 *
	 * @param array will show in json/将会以json格式返回的数据
	 * @param bool whether to terminate after response/返回之后是否立刻退出程序
	 */
	public function ajaxSucceed($data = null,$terminate = true)
	{
		$this->ajaxResponse(true,$data,$terminate);
	}

	/**
	 * shortcut of {@link ajaxResponse}
	 * ajaxResponse方法的快捷方式
	 *
	 * {
	 * 	succeed: false,
	 *  message: string,not null,
	 *  other data...
	 * }
	 *
	 * @param string will add in json decoded response/将会添加到以json格式返回的数据中
	 * @param bool whether to terminate after response/返回之后是否立刻退出程序
	 */
	public function ajaxFailed($message,$terminate = true)
	{
		$this->ajaxResponse(false,array('message'=>$message),$terminate);
	}

	/**
	 * get a "best" uri to redirect to
	 * 获得"最佳"的转向uri
	 *
	 * @param string or array {@see CHtml::normalizeUrl} where to redirect if no referrer,defaults to '/'
	 * 没有referrer的情况下跳转的方向，默认是首页
	 * @param string or array {@see CHtml::normalizeUrl} where will fall into dead loop if redirect to,defaults to current controller/action
	 * 会导致无限redirect的uri，默认是当前controller/action
	 */
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
			elseif (is_array($deadLoopUri))
			{
				$deadLoopUri = CHtml::normalizeUrl($deadLoopUri);
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

	/**
	 * redirect to the "best" uri
	 * 转向"最佳"uri
	 * @see {@link getAutoRedirectUri}
	 *
	 * @param string or array {@see CHtml::normalizeUrl} where to redirect if no referrer,defaults to '/'
	 * 没有referrer的情况下跳转的方向，默认是首页
	 * @param string or array {@see CHtml::normalizeUrl} where will fall into dead loop if redirect to,defaults to current controller/action
	 * 会导致无限redirect的uri，默认是当前controller/action
	 * @param bool whether terminate after redirect
	 */
	public function autoRedirect($defaultUri = null,$deadLoopUri = null,$terminate = null)
	{
		$this->redirect($this->getAutoRedirectUri($defaultUri,$deadLoopUri),$terminate);
	}
}