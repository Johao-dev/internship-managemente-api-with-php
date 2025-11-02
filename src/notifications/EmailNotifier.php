<?php

namespace App\notifications;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use App\config\SmtpConfig;
use App\core\ApiException;
use App\notifications\dtos\Email;

class EmailNotifier {

    private PHPMailer $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        SmtpConfig::configure($this->mailer);
    }

    public function send(Email $emailDto): bool {
        try {
            $this->mailer->addAddress($emailDto->toEmail, $emailDto->toName);

            $this->mailer->Subject = $emailDto->subject;
            $this->mailer->Body    = $emailDto->body;
            $this->mailer->AltBody = strip_tags($emailDto->body);

            return $this->mailer->send();
            
        } catch (PHPMailerException $e) {
            throw ApiException::internalServerError("No se pudo enviar el correo: " . $this->mailer->ErrorInfo);
        } finally {
            $this->mailer->clearAddresses();
        }
    }
}
