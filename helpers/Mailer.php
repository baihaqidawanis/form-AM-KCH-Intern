<?php
use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{
	protected $smtp_username = SMTP_USERNAME;
	protected $smtp_password = SMTP_PASSWORD;
	protected $smtp_host = SMTP_HOST;
	protected $smtp_port = SMTP_PORT;
	protected $smtp_secure = SMTP_SECURE;  // 'ssl' (port 465) atau 'tls' (port 587)


	protected $sender_email = DEFAULT_EMAIL;
	protected $sender_name = DEFAULT_EMAIL_ACCOUNT_NAME;


	public function __construct()
	{
		if (empty($this->smtp_port)) {
			$this->smtp_port = 465;
		}
	}

	public function send_mail($receipient_emails, $subject, $msg)
	{
		// PHPMailer 5.2 (2013) bundle lama di libs/PHPMailer/ dihapus -- pakai
		// `each()` yang udah dihapus dari PHP 8, jadi CRASH tiap kali beneran
		// nyoba kirim email (baru ketauan Round 43, karena USE_SMTP selalu
		// false sebelumnya jadi baris ini gak pernah beneran jalan). Sekarang
		// pakai phpmailer/phpmailer via Composer (v7.x, PHP 8 compatible).
		$mail = new PHPMailer;
		if (USE_SMTP == true) {
			//$mail->SMTPDebug = 3;                               // Enable verbose debug output
			$mail->isSMTP();                                      // Set mailer to use SMTP
			$mail->Host = $this->smtp_host;  // Specify main and backup SMTP servers
			$mail->SMTPAuth = true;                               // Enable SMTP authentication
			$mail->Username = $this->smtp_username;                 // SMTP username
			$mail->Password = $this->smtp_password;                           // SMTP password
			$mail->SMTPSecure = $this->smtp_secure;                            // Enable TLS encryption, `ssl` also accepted
			$mail->Port = $this->smtp_port;                                    // TCP port to connect to
		}

		$mail->From = $this->sender_email;
		$mail->FromName = $this->sender_name;

		if (is_array($receipient_emails)) {
			foreach ($receipient_emails as $email) {
				$mail->addAddress($email); // Add a recipient
			}
		} else {
			$mail->addAddress($receipient_emails); // Add a recipient
		}

		$mail->isHTML(true);                                  // Set email format to HTML
		$mail->Subject = $subject;
		$mail->Body    = $msg;
		$mail->AltBody = strip_tags($msg);
		if ($mail->send()) {
			return true;
		} else {
			return  $mail->ErrorInfo;
		}
	}
}
