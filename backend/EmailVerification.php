<?php
// backend/EmailVerification.php

// 1. تحديد المسار المطلق لمجلد PHPMailer
// بما أن الملف الحالي في backend/ ومجلد PHPMailer يفترض أن يكون في backend/PHPMailer/
$phpmailer_base = __DIR__ . '/PHPMailer/src/';

// 2. التحقق من وجود الملفات قبل استدعائها (لحمايتك من الـ Fatal Error)
if (file_exists($phpmailer_base . 'Exception.php')) {
    require_once $phpmailer_base . 'Exception.php';
    require_once $phpmailer_base . 'PHPMailer.php';
    require_once $phpmailer_base . 'SMTP.php';
} else {
    // إذا لم يجدها، سيعطيك رسالة واضحة في الـ Logs لتخبرني بها
    error_log("CRITICAL ERROR: PHPMailer folder not found at: " . $phpmailer_base);
    die("Error: PHPMailer files are missing. Please check folder structure.");
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// تعريف الـ Interface إذا لم يكن مستدعى
if (!interface_exists('VerificationStrategy')) {
    require_once 'VerificationStrategy.php';
}

class EmailVerification implements VerificationStrategy {
    public function sendCode($contact) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = getenv('EMAIL_USER'); 
            $mail->Password   = getenv('EMAIL_PASS'); 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($mail->Username, 'Ecommerce Store');
            $mail->addAddress($contact); 
            $mail->isHTML(true);
            $mail->Subject = 'كود تفعيل الحساب';
            
            $verification_code = rand(1000, 9999);
            $mail->Body = "كود التفعيل الخاص بك هو: <b>" . $verification_code . "</b>";

            if($mail->send()) {
                if (session_status() == PHP_SESSION_NONE) { session_start(); }
                $_SESSION['verification_code'] = $verification_code;
                return "success"; 
            }
        } catch (Exception $e) {
            error_log("Mail Error: " . $mail->ErrorInfo);
            return "فشل الإرسال: " . $mail->ErrorInfo;
        }
    }
}