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

    public function getAll(int $userLevel): array
    {
        $sql = "SELECT * FROM category_list WHERE category_read_level >= ? ORDER BY category_order ASC";
        return $this->db->fetchAll($sql, [$userLevel]);
    }

    public function getById(int $userLevel, int $categoryId): ?array
    {
        $sql = "SELECT * FROM category_list WHERE category_read_level >= ? AND category_index = ?";
        return $this->db->fetch($sql, [$userLevel, $categoryId]);
    }

    public function getReadableList(int $userLevel): ?array
    {
        $sql = "SELECT * FROM category_list WHERE category_read_level >= ?";
        return $this->db->fetch($sql, [$userLevel]);
    } 

    public function getWritableList(int $userLevel): ?array
    {
        $sql = "SELECT * FROM category_list WHERE category_write_level >= ?";
        return $this->db->fetch($sql, [$userLevel]);
    } 

    public function create(string $name, int $order = 0, int $readLevel = 0, int $writeLevel = 0): int
    {
        $sql = "INSERT INTO category_list (category_name, category_order, category_read_level, category_write_level) VALUES (?, ?, ?, ?)";
        $this->db->query($sql, [$name, $order, $readLevel, $writeLevel]);
        return (int)$this->db->lastInsertId();
    }

    public function delete(int $categoryId): bool
    {
        $sql = "SELECT COUNT(*) as count FROM posting_list WHERE category_index = ?";
        $result = $this->db->fetch($sql, [$categoryId]);
        
        if ($result && $result['count'] > 0) {
            return false;
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

    public function isWriteAuth(int $userLevel, int $categoryId): bool
    {
        $sql = "SELECT COUNT(*) as count FROM category_list WHERE category_read_level >= ? AND category_index = ?";
        $result = $this->db->fetch($sql, [$categoryId]);
        return ($result && $result['count'] > 0);
    }

    public function isReadAuth(int $userLevel, int $categoryId): bool
    {
        $sql = "SELECT COUNT(*) as count FROM category_list WHERE category_write_level >= ? AND category_index = ?";
        $result = $this->db->fetch($sql, [$categoryId]);
        return ($result && $result['count'] > 0);
    }
}
