<?php
// backend/AuthStrategy.php

/**
 * Interface AuthStrategy
 * يحدد العقد الذي يجب أن تتبعه جميع استراتيجيات التحقق
 */
interface AuthStrategy {
    public function verify($token);
}

/**
 * AdminAuth Strategy
 * استراتيجية خاصة بالتحقق من صلاحيات الأدمن فقط
 */
class AdminAuth implements AuthStrategy {
    private $jwt;

    public function __construct($jwtHandler) {
        $this->jwt = $jwtHandler;
    }

    public function verify($token) {
        $userData = $this->jwt->decodeToken($token);
        // يجب أن يكون التوكن صالحاً والدور هو Admin
        return ($userData && isset($userData['role']) && $userData['role'] === 'Admin');
    }
}

/**
 * CustomerAuth Strategy
 * استراتيجية خاصة بالتحقق من الزبائن العاديين
 */
class CustomerAuth implements AuthStrategy {
    private $jwt;

    public function __construct($jwtHandler) {
        $this->jwt = $jwtHandler;
    }

    public function verify($token) {
        $userData = $this->jwt->decodeToken($token);
        // يكفي أن يكون التوكن صالحاً (سواء زبون أو أدمن حسب منطق متجرك)
        return ($userData !== null);
    }
}

/**
 * AuthContext
 * الكلاس المسؤول عن تنفيذ الاستراتيجية المختارة دون التدخل في تفاصيلها
 */
class AuthContext {
    private $strategy;

    public function __construct(AuthStrategy $strategy) {
        $this->strategy = $strategy;
    }

    public function authenticate($token) {
        return $this->strategy->verify($token);
    }
}