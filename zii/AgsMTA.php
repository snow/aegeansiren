<?php
/**
 * provide a configured PHPMailer instance
 * 提供一个配置好的PHPMailer实例
 *
 * @author snow
 *
 */
class AgsMTA extends CComponent
{
	public $host,$user,$password,$name;

	public function init()
	{
		require_once dirname(__FILE__).'/class.phpmailer.php';
	}

	/**
	 * TODO: any way to avoid new instance every time?
	 *
	 * @return a configured PHPMailer instance
	 * 一个配置好的PHPMailer实例
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