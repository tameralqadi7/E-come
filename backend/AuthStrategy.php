<?php
// backend/AuthStrategy.php

interface AuthStrategy {
    public function verify($token);
}

class AdminAuth implements AuthStrategy {
    private $jwt;

    public function __construct($jwtHandler) {
        $this->jwt = $jwtHandler;
    }

    public function verify($token) {
        $userData = $this->jwt->decodeToken($token);
        // التحقق باستخدام strtolower لضمان عدم حدوث خطأ بسبب حالة الأحرف
        return ($userData && isset($userData['role']) && strtolower($userData['role']) === 'admin');
    }
}

class CustomerAuth implements AuthStrategy {
    private $jwt;

    public function __construct($jwtHandler) {
        $this->jwt = $jwtHandler;
    }

    public function verify($token) {
        $userData = $this->jwt->decodeToken($token);
        return ($userData !== null);
    }
}

class AuthContext {
    private $strategy;

    public function __construct(AuthStrategy $strategy) {
        $this->strategy = $strategy;
    }

    public function authenticate($token) {
        // تنظيف التوكن من كلمة Bearer قبل إرساله للتحقق
        $cleanToken = str_replace('Bearer ', '', $token);
        return $this->strategy->verify($cleanToken);
    }
}