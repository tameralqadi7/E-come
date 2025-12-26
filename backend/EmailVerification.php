<?php
// backend/strategies/EmailVerification.php
require_once 'VerificationStrategy.php';

// --- السطر الناقص والضروري جداً ---
// هذا السطر يخبر PHP بتحميل مكتبة PHPMailer
require_once __DIR__ . '/../../vendor/autoload.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
            $mail->CharSet    = 'UTF-8'; // لدعم اللغة العربية في الرسالة

            $mail->setFrom(getenv('EMAIL_USER'), 'Ecommerce Store');
            $mail->addAddress($contact); 

            $mail->isHTML(true);
            $mail->Subject = 'كود تفعيل الحساب';
            
            $verification_code = rand(1000, 9999);
            $mail->Body = "كود التفعيل الخاص بك هو: <b>" . $verification_code . "</b>";

            if($mail->send()) {
                return "تم إرسال كود التفعيل إلى الإيميل: " . $contact;
            }
            
        } catch (Exception $e) {
            return "فشل الإرسال: " . $mail->ErrorInfo;
        }
    }
}