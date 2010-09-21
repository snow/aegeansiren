<?php

class AgsMTA extends CComponent
{
	public $host,$user,$password,$name;

	public function init()
	{
		require_once dirname(__FILE__).'/class.phpmailer.php';
	}

	/**
	 * @return PHPMailer
	 */
	public function getMailer()
	{
		$mailer = new PHPMailer;

		if ($this->host)
		{
			$mailer->IsSMTP();
			$mailer->Host = $this->host;

			if ($this->user && $this->password)
			{
				$mailer->SMTPAuth = true;
				$mailer->Username = $this->user;
				$mailer->Password = $this->password;
			}
		}

		$mailer->SetFrom($this->user,$this->name,false);
		$mailer->IsHTML(true);
		$mailer->CharSet = 'utf-8';

		return $mailer;
	}
}