<?php
use PHPUnit\Framework\TestCase;

// افتراض أن لدينا كلاس منطق للمنتجات
class ProductTest extends TestCase {
    
    public function testProductPriceCannotBeNegative() {
        $price = -10;
        $isValid = ($price > 0);
        
        $this->assertFalse($isValid, "السعر لا يجب أن يكون سالباً");
    }

    public function testProductNameIsRequired() {
        $name = "";
        $isValid = !empty($name);
        
        $this->assertFalse($isValid, "اسم المنتج لا يجب أن يكون فارغاً");
    }
    
    public function testPasswordHashing() {
        $password = "123456";
        $hash = password_hash($password, PASSWORD_BCRYPT);
        
        $this->assertTrue(password_verify($password, $hash), "تشفير كلمة السر يجب أن يكون متطابقاً");
    }
}