<?php
class JWTHandler {
    private $secret_key = "my_super_secret_key_123"; // مفتاح سري للتشفير

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
}
