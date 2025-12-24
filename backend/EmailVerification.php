<?php
// backend/strategies/EmailVerification.php
require_once 'VerificationStrategy.php';

class EmailVerification implements VerificationStrategy {
    public function sendCode($contact) {
        // هنا نضع منطق إرسال الإيميل لاحقاً
        return "تم إرسال كود التفعيل إلى الإيميل: " . $contact;
    }
}