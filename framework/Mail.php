<?php

require "vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mail
{
    private $mail = null;

    public function __construct($emailTo, $subject, $template)
    {
        $this->mail = new PHPMailer(true);

        $this->mail->SMTPDebug = 0;
        $this->mail->isSMTP();
        $this->mail->Host = MAIL_HOST;
        $this->mail->SMTPAuth = true;
        $this->mail->Username = MAIL_USERNAME;
        $this->mail->Password = MAIL_PASSWORD;
        $this->mail->SMTPSecure = "tls";
        $this->mail->Port = MAIL_PORT;
        $this->mail->CharSet = "UTF-8";

        $this->mail->setFrom(MAIL_SENDER, WEBSITE_TITLE);
        $this->mail->addAddress($emailTo);

        $this->mail->isHTML(true);
        $this->mail->Subject = $subject;
        $this->mail->Body = $template;
    }

    public function send()
    {
        try {
            $this->mail->send();
            ob_clean();
            return true;
        } catch(Exception $ex) {
            Response::badRequest($ex->getMessage(), $ex->getTrace())->send();
        }
    }
}