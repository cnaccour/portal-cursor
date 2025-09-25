<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function send_smtp_email(string $to, string $subject, string $html, string $text = ''): array {
    $config = require __DIR__ . '/../config/config.php';
    $smtp = $config['SMTP'];

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $smtp['host'];
        $mail->Port = (int)$smtp['port'];
        $mail->SMTPAuth = !empty($smtp['user']);
        $secure = $smtp['secure'] ?? '';
        if ($secure) { $mail->SMTPSecure = $secure; }
        if ($mail->SMTPAuth) {
            $mail->Username = $smtp['user'];
            $mail->Password = $smtp['pass'];
        }
        $mail->setFrom($smtp['from_email'], $smtp['from_name']);
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->isHTML(true);
        $mail->Body = $html;
        if ($text) { $mail->AltBody = $text; }
        $mail->send();
        return ['success' => true];
    } catch (Exception $e) {
        return ['success' => false, 'error' => $mail->ErrorInfo];
    }
}
