<?php
// backend/strategies/EmailVerification.php
require_once 'VerificationStrategy.php';

/**
 * استدعاء ملفات PHPMailer 7.0.1 يدوياً
 * بما أن الملف الحالي في backend/strategies/
 * فنحن نخرج خطوة واحدة لنجد مجلد PHPMailer
 */
require_once __DIR__ . '/../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailVerification implements VerificationStrategy {
    public function sendCode($contact) {
        $mail = new PHPMailer(true);

        try {
            // إعدادات السيرفر
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            
            // جلب البيانات من Environment Variables في Render
            $mail->Username   = getenv('EMAIL_USER'); 
            $mail->Password   = getenv('EMAIL_PASS'); 
            
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            // المستلم
            $mail->setFrom($mail->Username, 'Ecommerce Store');
            $mail->addAddress($contact); 

            // المحتوى
            $mail->isHTML(true);
            $mail->Subject = 'كود تفعيل الحساب';
            
            $verification_code = rand(1000, 9999);
            $mail->Body = "
                <div style='direction: rtl; font-family: tahoma; border: 1px solid #ddd; padding: 20px; border-radius: 10px;'>
                    <h2 style='color: #333;'>مرحباً بك في متجرنا</h2>
                    <p>شكراً لتسجيلك. كود التفعيل الخاص بك هو:</p>
                    <h1 style='color: #4CAF50; background: #f9f9f9; display: inline-block; padding: 10px 20px; border-radius: 5px;'> " . $verification_code . " </h1>
                    <p style='font-size: 12px; color: #777;'>إذا لم تطلب هذا الكود، يرجى تجاهل الرسالة.</p>
                </div>";

            if($mail->send()) {
                // تخزين الكود في الجلسة
                if (session_status() == PHP_SESSION_NONE) { session_start(); }
                $_SESSION['verification_code'] = $verification_code;
                $_SESSION['email_temp'] = $contact;
                
                return "success"; 
            }
            
        } catch (Exception $e) {
            error_log("Mail Error: " . $mail->ErrorInfo);
            return "فشل الإرسال: " . $mail->ErrorInfo;
        }
    }
}