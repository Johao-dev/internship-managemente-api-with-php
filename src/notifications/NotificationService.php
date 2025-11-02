<?php

namespace App\notifications;

use App\core\EmailTemplateBuilder;
use App\notifications\dtos\Email;
use App\users\UserEntity;
use Exception;

class NotificationService {

    private EmailNotifier $emailNotifier;

    public function __construct() {
        $this->emailNotifier = new EmailNotifier();
    }

    public function sendWelcomeEmail(UserEntity $user, string $plainPassword): void {
        try {
            $emailDto = new Email();
            $emailDto->toEmail = $user->institutional_email;
            $emailDto->toName = $user->full_name;
            $emailDto->subject = "¡Bienvenido a la Plataforma de Practicantes de CONAUTI S.A.C.!";
            
            $emailDto->body = EmailTemplateBuilder::getWelcomeEmailBody(
                $user->full_name,
                $user->institutional_email,
                $plainPassword
            );
            
            $this->emailNotifier->send($emailDto);

        } catch (Exception $e) {
            error_log("Fallo al enviar el correo de bienvenida a {$user->institutional_email}: " . $e->getMessage());
        }
    }
}