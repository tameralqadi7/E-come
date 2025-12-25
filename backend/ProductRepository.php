<?php
// backend/ProductRepository.php

class ProductRepository {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    // جلب كل المنتجات (للعرض في الجدول)
    public function getAll() {
        $stmt = $this->db->query("SELECT p.*, c.name as category_name 
                                 FROM products p 
                                 LEFT JOIN categories c ON p.category_id = c.id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // إضافة منتج جديد
    public function create($data) {
        $sql = "INSERT INTO products (name, description, price, category_id, image_url) 
                VALUES (:name, :description, :price, :category_id, :image_url)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':name' => $data['name'],
            ':description' => $data['description'],
            ':price' => $data['price'],
            ':category_id' => $data['category_id'],
            ':image_url' => $data['image_url']
        ]);
    }

    // حذف منتج
    public function delete($id) {
        $sql = "DELETE FROM products WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    // تحديث منتج (إذا قررت إضافة ميزة التعديل لاحقاً)
    public function update($id, $data) {
        $sql = "UPDATE products SET name = :name, price = :price WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':name' => $data['name'],
            ':price' => $data['price'],
            ':id' => $id
        ]);
    }
}