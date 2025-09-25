<?php
// Try to load PHPMailer; if unavailable, we'll fallback to mail()
@ini_set('display_errors', '0');
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../includes/PHPMailer/src/PHPMailer.php')) {
    require_once __DIR__ . '/../includes/PHPMailer/src/Exception.php';
    require_once __DIR__ . '/../includes/PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/../includes/PHPMailer/src/SMTP.php';
}

function send_smtp_email(string $to, string $subject, string $body, string $altBody = ''): array {
    if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'server.jjosephsalon.com';
            $mail->Port = 587;
            $mail->SMTPAuth = true;
            // Use STARTTLS if supported by installed PHPMailer
            if (defined('PHPMailer\\PHPMailer\\PHPMailer::ENCRYPTION_STARTTLS')) {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = 'tls';
            }
            $mail->Username = 'noreply@portal.jjosephsalon.com';
            $mail->Password = 'u~MItj[l@Ov~IokK';

            // Explicit From/Reply-To with display name
            $mail->setFrom('noreply@portal.jjosephsalon.com', 'J Joseph Portal');
            $mail->addReplyTo('noreply@portal.jjosephsalon.com', 'J Joseph Portal');
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body = $body;
            if ($altBody !== '') { $mail->AltBody = $altBody; }
            $mail->send();
            return ['success' => true];
        } catch (\Throwable $e) {
            $err = method_exists($mail ?? null, 'ErrorInfo') ? ($mail->ErrorInfo ?: $e->getMessage()) : $e->getMessage();
            return ['success' => false, 'error' => $err];
        }
    }
    // Fallback: use mail()
    $headers = [];
    $headers[] = 'From: J Joseph Portal <noreply@portal.jjosephsalon.com>';
    $headers[] = 'Reply-To: J Joseph Portal <noreply@portal.jjosephsalon.com>';
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=utf-8';
    $sent = @mail($to, $subject, $body, implode("\r\n", $headers));
    return $sent ? ['success' => true] : ['success' => false, 'error' => 'mail() failed and PHPMailer unavailable'];
}
