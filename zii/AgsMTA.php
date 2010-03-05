<?php

class AgsMTA extends CComponent
{
	public $host,$user,$password;

	public function init()
	{
		require_once dirname(__FILE__).'/class.phpmailer.php';
	}

	/**
	 * @param $params associate array of params listed below
	 * @param $to array of array('addr'=>'nobody@neverland.com','name'=>'Nobody')
	 * @param $bcc array of array('addr'=>'nobody@neverland.com','name'=>'Nobody')
	 * @param string $subject
	 * @param string $body
	 * @param $from array('addr'=>'nobody@neverland.com','name'=>'Nobody')
	 */
	public function send($params = array())
	{
		$params = array_merge(array(
			'to'=>array(),
			'cc'=>array(),
			'bcc'=>array(),
			'from'=>array(
				'addr'=>Yii::app()->params['sysmailAddr'],
				'name'=>Yii::app()->params['sysmailName'],
			),
		),$params);

		$mailer = new PHPMailer;

		$mailer->IsSMTP();
		$mailer->SMTPAuth = true;
		$mailer->Host = $this->host;
		$mailer->Username = $this->user;
		$mailer->Password = $this->password;

		$mailer->SetFrom($params['from']['addr'],$params['from']['name']);

		foreach (array('to','bcc','cc') as $sendType)
		{
			if (isset($params[$sendType]['addr']))
			{
				$params[$sendType] = array($params[$sendType]);
			}
			foreach ($params[$sendType] as $addr)
			{
				if (filter_var($addr['addr'],FILTER_VALIDATE_EMAIL))
				{
					switch ($sendType)
					{
						default:
							$mailer->AddAddress($addr['addr'],$addr['name']);
						break;

						case 'bcc':
							$mailer->AddBCC($addr['addr'],$addr['name']);
						break;

						case 'cc':
							$mailer->AddCC($addr['addr'],$addr['name']);
						break;
					}
				}
			}
		}

		$mailer->Subject = $params['subject'];
		$mailer->IsHTML(true);
		$mailer->CharSet = 'utf-8';
		$mailer->Body = $params['body'];

		return $mailer->Send();
	}

	public function getMailer()
	{
		$mailer = new PHPMailer;

		$mailer->IsSMTP();
		$mailer->SMTPAuth = true;
		$mailer->Host = $this->host;
		$mailer->Username = $this->user;
		$mailer->Password = $this->password;
		$mailer->IsHTML(true);
		$mailer->CharSet = 'utf-8';

		return $mailer;
	}
}