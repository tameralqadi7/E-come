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
            
            // سحب البيانات من متغيرات البيئة في Render لضمان الأمان
            $mail->Username   = getenv('EMAIL_USER'); // إيميلك الحقيقي المضاف في Render
            $mail->Password   = getenv('EMAIL_PASS'); // App Password المكون من 16 حرفاً
            
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // المستلم والمرسل
            $mail->setFrom(getenv('EMAIL_USER'), 'Ecommerce Store');
            $mail->addAddress($contact); 

            // محتوى الإيميل
            $mail->isHTML(true);
            $mail->Subject = 'Verification Code';
            
            // توليد كود عشوائي
            $verification_code = rand(1000, 9999);
            $mail->Body = "Your verification code is: <b>" . $verification_code . "</b>";

            // إرسال الإيميل
            if($mail->send()) {
                return "تم إرسال كود التفعيل إلى الإيميل: " . $contact;
            }
            
        } catch (Exception $e) {
            // في حال الفشل، يرجع رسالة الخطأ لتسهيل تتبعها في Render Logs
            return "فشل الإرسال: " . $mail->ErrorInfo;
        }
    }
}