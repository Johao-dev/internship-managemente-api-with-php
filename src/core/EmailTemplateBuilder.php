<?php

namespace App\core;

class EmailTemplateBuilder {

    public static function getWelcomeEmailBody(string $name, string $email, string $plainPassword): string {
        return "
            <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
                <h2>¡Bienvenido a CONAUTI S.A.C., {$name}!</h2>
                <p>Nos complace darte la bienvenida a la plataforma de gestión de practicantes.</p>
                <p>Tu cuenta ha sido creada exitosamente. A continuación, encontrarás tus credenciales de acceso para que puedas ingresar al sistema:</p>
                
                <table style='width: auto; border-collapse: collapse; margin: 20px 0; border: 1px solid #ddd;'>
                    <tr style='border-bottom: 1px solid #ddd;'>
                        <td style='padding: 10px; font-weight: bold; background-color: #f9f9f9;'>Usuario:</td>
                        <td style='padding: 10px;'>{$email}</td>
                    </tr>
                    <tr style='border-bottom: 1px solid #ddd;'>
                        <td style='padding: 10px; font-weight: bold; background-color: #f9f9f9;'>Contraseña:</td>
                        <td style='padding: 10px;'>{$plainPassword}</td>
                    </tr>
                </table>
                
                <p>Te recomendamos cambiar tu contraseña después de tu primer inicio de sesión.</p>
                <p>¡Esperamos que tengas una gran experiencia!</p>
                <br>
                <p>Atentamente,<br>El equipo de CONAUTI S.A.C.</p>
            </div>
        ";
    }
}