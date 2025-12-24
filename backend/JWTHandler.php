<?php
// backend/config/JWTHandler.php

class JWTHandler {
    private $secret_key = "my_super_secret_key_123"; // مفتاح سري للتشفير

    // 1. دالة إنشاء التوكن (Generate Token) - تستخدم عند تسجيل الدخول أو التسجيل
    public function generateToken($data) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode($data);

        // تحويل البيانات لـ Base64
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        // إنشاء التوقيع (Signature)
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->secret_key, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    // 2. الدالة الجديدة: فحص التوكن واستخراج البيانات (Decode/Validate Token)
    // هذه الدالة تحقق المهمة: Security: JWT Validation & Role Extraction
    public function decodeToken($token) {
        // تقسيم التوكن إلى أجزائه الثلاثة
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false; // التوكن غير صالح بتاتا
        }

        $header = $parts[0];
        $payload = $parts[1];
        $signatureProvided = $parts[2];

        // إعادة حساب التوقيع (Signature) للتأكد من سلامة التوكن وعدم التلاعب به
        $signatureCheck = hash_hmac('sha256', $header . "." . $payload, $this->secret_key, true);
        $base64UrlSignatureCheck = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signatureCheck));

        // التحقق مما إذا كان التوقيع المحسوب يطابق التوقيع القادم من المستخدم
        if ($base64UrlSignatureCheck === $signatureProvided) {
            // فك تشفير البيانات (Payload) وإرجاعها كمصفوفة (Array)
            $decodedPayload = base64_decode(str_replace(['-', '_'], ['+', '/'], $payload));
            return json_decode($decodedPayload, true);
        }

        return false; // التوقيع لا يطابق (التوكن مزور)
    }
}