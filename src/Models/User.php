<?php

namespace Blog\Models;

use Blog\Database\Database;

class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function authenticate(string $userId, string $password): ?array
    {
        $sql = "SELECT * FROM user_list WHERE user_id = ? AND user_pw = ? AND user_state = 0";
        $user = $this->db->fetch($sql, [$userId, $password]);
        
        if ($user) {
            $this->updateLastAction($user['user_index']);
            return $user;
        }
        
        return null;
    }

    public function getUserById(string $userId): ?array
    {
        $sql = "SELECT * FROM user_list WHERE user_id = ?";
        return $this->db->fetch($sql, [$userId]);
    }

    public function updateLastAction(int $userIndex): void
    {
        $sql = "UPDATE user_list SET user_last_action_datetime = NOW() WHERE user_index = ?";
        $this->db->query($sql, [$userIndex]);
    }

    public function canWrite(int $userIndex): bool
    {
        $sql = "SELECT user_posting_count, user_posting_limit FROM user_list WHERE user_index = ?";
        $user = $this->db->fetch($sql, [$userIndex]);
        
        if (!$user) {
            return false;
        }
        
        return $user['user_posting_count'] < $user['user_posting_limit'];
    }

    public function incrementPostCount(int $userIndex): void
    {
        $sql = "UPDATE user_list SET user_posting_count = user_posting_count + 1 WHERE user_index = ?";
        $this->db->query($sql, [$userIndex]);
    }

    public function getVisitorCount(): int
    {
        $visitYear = date("Y");
        $visitWeek = date("W");
        $yearWeek = $visitYear . str_pad($visitWeek, 2, '0', STR_PAD_LEFT);
        
        $sql = "SELECT visit_count FROM weekly_visitors WHERE year_week = ?";
        $result = $this->db->fetch($sql, [$yearWeek]);
        return $result ? (int)$result['visit_count'] : 0;
    }

    public function updateVisitorCount(): void
    {
        $visitYear = date("Y");
        $visitWeek = date("W");
        $yearWeek = $visitYear . str_pad($visitWeek, 2, '0', STR_PAD_LEFT);
        
        $sql = "INSERT INTO weekly_visitors VALUES (?, 1) ON DUPLICATE KEY UPDATE visit_count = visit_count + 1";
        $this->db->query($sql, [$yearWeek]);
    }
}
