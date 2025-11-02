<?php

namespace App\config;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use App\core\ApiException;

class SmtpConfig {

    public static function configure(PHPMailer &$mailer): void {
        try {
            $mailer->isSMTP();
            $mailer->Host       = $_ENV['EMAIL_HOST'];
            $mailer->SMTPAuth   = true;
            $mailer->Username   = $_ENV['EMAIL_USER'];
            $mailer->Password   = $_ENV['EMAIL_PASSWORD'];
            $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mailer->Port       = (int)($_ENV['EMAIL_PORT']);

            if (empty($mailer->Username) || empty($mailer->Password)) {
                throw ApiException::internalServerError("Las credenciales de correo (EMAIL_USER, EMAIL_PASSWORD) no están configuradas en el .env");
            }
            
            $fromName = $_ENV['EMAIL_FROM_NAME'];
            $mailer->setFrom($mailer->Username, $fromName);

            $mailer->isHTML(true);
            $mailer->CharSet = 'UTF-8';

        } catch (PHPMailerException $e) {
            throw ApiException::internalServerError("Error en la configuración del Mailer: " . $e->getMessage());
        }
    }
}