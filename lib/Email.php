<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Try Composer autoload; if unavailable, include PHPMailer manually
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    require_once __DIR__ . '/../includes/PHPMailer/src/Exception.php';
    require_once __DIR__ . '/../includes/PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/../includes/PHPMailer/src/SMTP.php';
}

function send_smtp_email(string $to, string $subject, string $body, string $altBody = ''): array {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'server.jjosephsalon.com';
        $mail->Port = 587;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Username = 'noreply@jjosephsalon.com';
        $mail->Password = 'jjsadmin81';

        $mail->setFrom('noreply@jjosephsalon.com', 'noreply@jjosephsalon.com');
        $mail->addReplyTo('noreply@jjosephsalon.com');
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->isHTML(true);
        $mail->Body = $body;
        if ($altBody !== '') { $mail->AltBody = $altBody; }
        $mail->send();
        return ['success' => true];
    } catch (Exception $e) {
        return ['success' => false, 'error' => $mail->ErrorInfo];
    }
}
