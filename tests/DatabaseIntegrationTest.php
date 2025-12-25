<?php
use PHPUnit\Framework\TestCase;

class DatabaseIntegrationTest extends TestCase {
    private $pdo;

    // يتم تشغيل هذه الدالة قبل كل اختبار لفتح اتصال بالقاعدة
    protected function setUp(): void {
        $host = 'db'; // اسم الخدمة في دوكر
        $db   = 'ecommerce_db';
        $user = 'root';
        $pass = 'root_password';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        try {
            $this->pdo = new PDO($dsn, $user, $pass);
        } catch (\PDOException $e) {
            $this->markTestSkipped("تعذر الاتصال بقاعدة البيانات: " . $e->getMessage());
        }
    }

    // اختبار: هل يمكننا إضافة صنف (Category) وقراءته؟
    public function testDatabaseInsertAndSelect() {
        // 1. إضافة صنف تجريبي
        $testName = "TestCategory_" . time();
        $stmt = $this->pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$testName]);

        // 2. محاولة قراءته من القاعدة
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE name = ?");
        $stmt->execute([$testName]);
        $category = $stmt->fetch();

        // 3. التأكد من النتائج
        $this->assertNotEmpty($category);
        $this->assertEquals($testName, $category['name']);

        // 4. تنظيف القاعدة (حذف البيانات التجريبية)
        $this->pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$category['id']]);
    }
}