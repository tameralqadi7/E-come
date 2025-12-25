<?php
// backend/strategies/EmailVerification.php
require_once 'VerificationStrategy.php';
// تأكد من تحميل مكتبة PHPMailer عبر Composer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailVerification implements VerificationStrategy {
    public function sendCode($contact) {
        $mail = new PHPMailer(true);

        try {
            // إعدادات السيرفر (SMTP)
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = 'your-email@gmail.com'; // إيميلك
            $mail->Password   = 'your-app-password';    // كلمة سر التطبيق
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // المستلم
            $mail->setFrom('your-email@gmail.com', 'Ecommerce Store');
            $mail->addAddress($contact); 

            // محتوى الإيميل
            $mail->isHTML(true);
            $mail->Subject = 'Verification Code';
            $mail->Body    = "Your verification code is: <b>" . rand(1000, 9999) . "</b>";

            $mail->send();
            return "تم إرسال كود التفعيل إلى الإيميل: " . $contact;
        } catch (Exception $e) {
            return "فشل الإرسال: " . $mail->ErrorInfo;
        }
    }
}