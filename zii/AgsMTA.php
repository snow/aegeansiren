<?php

class AgsMTA extends CComponent
{
	public $host,$user,$password;

	public function init() {}

	public function send($to,$subject,$body,$from = '',$fromName = ''){

		require_once 'class.phpmailer.php';

		$mailer = new PHPMailer;

		$mailer->IsSMTP();
		$mailer->SMTPAuth = true;
		$mailer->Host = $this->host;
		$mailer->Username = $this->user;
		$mailer->Password = $this->password;

		$mailer->SetFrom($from?$from:Yii::app()->params['sysmailAddr'],
			$fromName?$fromName:Yii::app()->params['sysmailName']);

		if (!is_array($to))
		{
			$to = array($to);
		}
		foreach ($to as $e)
		{
			$mailer->AddAddress($e);
		}
		$mailer->Subject = $subject;
		$mailer->IsHTML(true);
		$mailer->CharSet = 'utf-8';
		$mailer->Body = $body;

		return $mailer->Send();
	}
}