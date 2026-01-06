<?php
/**
 * Configuration pour l'envoi d'emails
 * Utilise PHPMailer pour l'envoi via SMTP
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Si vous n'avez pas PHPMailer, installez-le avec: composer require phpmailer/phpmailer
// Ou téléchargez-le depuis: https://github.com/PHPMailer/PHPMailer
require_once 'vendor/autoload.php'; // Ajustez le chemin selon votre installation

/**
 * Configuration SMTP - MODIFIEZ CES VALEURS SELON VOTRE SERVEUR
 */
define('SMTP_HOST', 'smtp.gmail.com');  // Serveur SMTP (Gmail, Outlook, etc.)
define('SMTP_PORT', 587);                // Port SMTP (587 pour TLS, 465 pour SSL)
define('SMTP_USERNAME', 'hosneldeguenon@gmail.com'); // Votre email
define('SMTP_PASSWORD', 'vmdg xivb sicm wjny'); // Mot de passe d'application
define('SMTP_FROM_EMAIL', 'hosneldeguenon@gmail.com'); // Email expéditeur
define('SMTP_FROM_NAME', 'NelcoLaverie'); // Nom de l'expéditeur

/**
 * Envoie un code de réinitialisation par email
 * 
 * @param string $toEmail Email du destinataire
 * @param string $toName Prénom du destinataire
 * @param string $code Code à 6 chiffres
 * @return bool True si l'email est envoyé, False sinon
 */
function sendResetCode($toEmail, $toName, $code) {
    try {
        error_log("📧 Tentative d'envoi email à: $toEmail");
        
        $mail = new PHPMailer(true);
        
        // Configuration SMTP
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        
        // Désactiver la vérification SSL en développement (à retirer en production)
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Expéditeur et destinataire
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($toEmail, $toName);
        $mail->addReplyTo(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        
        // Contenu de l'email
        $mail->isHTML(true);
        $mail->Subject = 'Code de réinitialisation de mot de passe';
        
        // Corps de l'email en HTML
        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(90deg, #3b82f6, #60a5fa); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
                .code-box { background: white; border: 2px solid #3b82f6; border-radius: 10px; padding: 20px; text-align: center; margin: 20px 0; }
                .code { font-size: 36px; font-weight: bold; color: #3b82f6; letter-spacing: 8px; }
                .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 5px; }
                .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🔐 Réinitialisation de mot de passe</h1>
                </div>
                <div class="content">
                    <p>Bonjour <strong>' . htmlspecialchars($toName) . '</strong>,</p>
                    <p>Vous avez demandé à réinitialiser votre mot de passe. Voici votre code de vérification :</p>
                    
                    <div class="code-box">
                        <div class="code">' . $code . '</div>
                    </div>
                    
                    <p><strong>Ce code est valable pendant 30 minutes.</strong></p>
                    
                    <div class="warning">
                        <strong>⚠️ Important :</strong> Si vous n\'avez pas demandé cette réinitialisation, ignorez cet email. Votre mot de passe actuel reste sécurisé.
                    </div>
                    
                    <p>Cordialement,<br>L\'équipe ' . SMTP_FROM_NAME . '</p>
                </div>
                <div class="footer">
                    <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
                </div>
            </div>
        </body>
        </html>
        ';
        
        // Version texte alternatif
        $mail->AltBody = "Bonjour $toName,\n\n"
                       . "Votre code de réinitialisation : $code\n\n"
                       . "Ce code est valable pendant 30 minutes.\n\n"
                       . "Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.\n\n"
                       . "Cordialement,\nL'équipe " . SMTP_FROM_NAME;
        
        // Envoyer l'email
        $mail->send();
        
        error_log("✅ Email envoyé avec succès à: $toEmail");
        return true;
        
    } catch (Exception $e) {
        error_log("❌ Erreur envoi email: " . $mail->ErrorInfo);
        error_log("❌ Exception: " . $e->getMessage());
        return false;
    }
}

/**
 * ALTERNATIVE : Fonction simple avec mail() de PHP (moins fiable)
 * Décommentez cette fonction si vous ne pouvez pas utiliser PHPMailer
 */
/*
function sendResetCode($toEmail, $toName, $code) {
    try {
        error_log("📧 Envoi email simple à: $toEmail");
        
        $subject = "Code de réinitialisation de mot de passe";
        
        $message = "Bonjour $toName,\n\n";
        $message .= "Votre code de réinitialisation : $code\n\n";
        $message .= "Ce code est valable pendant 30 minutes.\n\n";
        $message .= "Cordialement,\nL'équipe";
        
        $headers = "From: " . SMTP_FROM_EMAIL . "\r\n";
        $headers .= "Reply-To: " . SMTP_FROM_EMAIL . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        $result = mail($toEmail, $subject, $message, $headers);
        
        if ($result) {
            error_log("✅ Email envoyé avec succès");
            return true;
        } else {
            error_log("❌ Échec de l'envoi de l'email");
            return false;
        }
        
    } catch (Exception $e) {
        error_log("❌ Erreur: " . $e->getMessage());
        return false;
    }
}
*/
?>