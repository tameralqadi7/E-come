<?php
// backend/strategies/EmailVerification.php
require_once 'VerificationStrategy.php';

// --- تعديل المسار ليكون أكثر مرونة ---
// نبحث عن الملف في المجلد الرئيسي للمشروع
$autoloadPath = __DIR__ . '/../../vendor/autoload.php';

if (!file_exists($autoloadPath)) {
    // محاولة أخرى في حال كانت الهيكلة مختلفة في السيرفر
    $autoloadPath = $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
}

if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} else {
    // تسجيل خطأ في الـ Logs إذا لم يجد الملف لتعرف مكانه بالضبط
    error_log("PHPMailer Autoload not found at: " . $autoloadPath);
}

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
            
            // استخدام المتغيرات البيئية أو كتابتها مباشرة (يفضلgetenv)
            $mail->Username   = getenv('EMAIL_USER') ?: 'your-email@gmail.com'; 
            $mail->Password   = getenv('EMAIL_PASS') ?: 'your-app-password'; 
            
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
                <div style='direction: rtl; font-family: tahoma;'>
                    <h2>مرحباً بك في متجرنا</h2>
                    <p>كود التفعيل الخاص بك هو:</p>
                    <h1 style='color: #4CAF50;'> " . $verification_code . " </h1>
                </div>";

            if($mail->send()) {
                // تخزين الكود في الجلسة (Session) للتحقق منه لاحقاً
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