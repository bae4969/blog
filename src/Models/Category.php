<?php

namespace Blog\Models;

use Blog\Database\Database;

class Category
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(int $read_level): array
    {
        $sql = "SELECT * FROM category_list WHERE category_read_level >= ? ORDER BY category_order ASC";
        return $this->db->fetchAll($sql, [$read_level]);
    }

    public function getById(int $categoryId): ?array
    {
        $sql = "SELECT * FROM category_list WHERE category_index = ?";
        return $this->db->fetch($sql, [$categoryId]);
    }

    public function create(string $name, int $order = 0, int $readLevel = 0, int $writeLevel = 0): int
    {
        $sql = "INSERT INTO category_list (category_name, category_order, category_read_level, category_write_level) VALUES (?, ?, ?, ?)";
        $this->db->query($sql, [$name, $order, $readLevel, $writeLevel]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $categoryId, string $name, int $order = 0, int $readLevel = 0, int $writeLevel = 0): bool
    {
        $sql = "UPDATE category_list SET category_name = ?, category_order = ?, category_read_level = ?, category_write_level = ? WHERE category_index = ?";
        $stmt = $this->db->query($sql, [$name, $order, $readLevel, $writeLevel, $categoryId]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $categoryId): bool
    {
        // 해당 카테고리의 게시글이 있는지 확인
        $sql = "SELECT COUNT(*) as count FROM posting_list WHERE category_index = ?";
        $result = $this->db->fetch($sql, [$categoryId]);
        
        if ($result && $result['count'] > 0) {
            return false; // 게시글이 있으면 삭제 불가
        }

        $sql = "DELETE FROM category_list WHERE category_index = ?";
        $stmt = $this->db->query($sql, [$categoryId]);
        return $stmt->rowCount() > 0;
    }

    public function getPostCount(int $categoryId): int
    {
        $sql = "SELECT COUNT(*) as count FROM posting_list WHERE category_index = ?";
        $result = $this->db->fetch($sql, [$categoryId]);
        return $result ? (int)$result['count'] : 0;
    }
}
