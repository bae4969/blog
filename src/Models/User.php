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
        $sql = "SELECT user_level FROM user_list WHERE user_index = ?";
        $user = $this->db->fetch($sql, [$userIndex]);
        
        if (!$user) {
            return false;
        }
        
        return $user['user_level'] <= 3;
    }

    public function getPostingLimitInfo(int $userIndex): ?array
    {
        $sql = "SELECT user_posting_count, user_posting_limit FROM user_list WHERE user_index = ?";
        $user = $this->db->fetch($sql, [$userIndex]);
        
        if (!$user) {
            return null;
        }
        
        return [
            'current_count' => (int)$user['user_posting_count'],
            'limit' => (int)$user['user_posting_limit'],
            'is_limited' => $user['user_posting_count'] >= $user['user_posting_limit']
        ];
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

    public function updateVisitorCount(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $visitYear = date("Y");
        $visitWeek = date("W");
        $yearWeek = $visitYear . str_pad($visitWeek, 2, '0', STR_PAD_LEFT);
        $sessionKey = 'visitor_counted_' . $yearWeek;
        
        // 오래된 주의 세션 키들 정리 (현재 주가 아닌 것들)
        if (isset($_SESSION)) {
            foreach ($_SESSION as $key => $value) {
                if (strpos($key, 'visitor_counted_') === 0) {
                    $sessionYearWeek = substr($key, 16);
                    if ($sessionYearWeek !== $yearWeek) {
                        unset($_SESSION[$key]);
                    }
                }
            }
        }
        
        if (isset($_SESSION[$sessionKey])) {
            return false;
        }
        
        $sql = "INSERT INTO weekly_visitors VALUES (?, 1) ON DUPLICATE KEY UPDATE visit_count = visit_count + 1";
        $this->db->query($sql, [$yearWeek]);
        
        $_SESSION[$sessionKey] = true;
        
        return true;
    }
}
