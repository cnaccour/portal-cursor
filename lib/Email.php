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
        // Enable SMTP debug logging (to PHP error_log)
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = 'error_log';
            // Explicit SMTP configuration
            $mail->isSMTP();
            $mail->SMTPAuth = true;
            $mail->Host = 'mail.jjosephsalon.com';
            $mail->Port = 587;
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Username = 'noreply@portal.jjosephsalon.com';
            $mail->Password = 'u~MItj[l@Ov~IokK';

            // Force From/Reply-To with display name
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
    // No PHPMailer available
    return ['success' => false, 'error' => 'PHPMailer not available'];
}
