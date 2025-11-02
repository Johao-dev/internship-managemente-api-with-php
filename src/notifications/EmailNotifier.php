<?php

namespace App\notification;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use App\core\ApiException;
use App\notifications\dtos\Email;

class EmailNotifier {

    private PHPMailer $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->configureSmtp();
    }

    private function configureSmtp(): void {
        try {
            $this->mailer->isSMTP();
            $this->mailer->Host       = getenv('EMAIL_HOST') ?: 'smtp.gmail.com';
            $this->mailer->SMTPAuth   = true;
            $this->mailer->Username   = getenv('EMAIL_USER');
            $this->mailer->Password   = getenv('EMAIL_PASSWORD');
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port       = (int)getenv('EMAIL_PORT') ?: 587;

            $fromEmail = getenv('EMAIL_USER');
            $fromName = getenv('EMAIL_FROM_NAME') ?: 'Sistema CONAUTI';
            $this->mailer->setFrom($fromEmail, $fromName);

            $this->mailer->isHTML(true);
            $this->mailer->CharSet = 'UTF-8';
        } catch (PHPMailerException $e) {
            throw ApiException::internalServerError("Error en la configuración del Mailer: " . $e->getMessage());
        }
    }

    public function send(Email $emailDto): bool {
        try {
            $this->mailer->addAddress($emailDto->toEmail, $emailDto->toName);

            $this->mailer->Subject = $emailDto->subject;
            $this->mailer->Body    = $emailDto->body;
            $this->mailer->AltBody = strip_tags($emailDto->body);

            $this->mailer->send();
            return true;
        } catch (PHPMailerException $e) {
            throw ApiException::internalServerError("No se pudo enviar el correo: " . $this->mailer->ErrorInfo);
        }
    }
}
