<?php

namespace Blog\Models;

use Blog\Database\Database;

class Post
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(int $userLevel, int $page = 1, int $perPage = 10, ?int $categoryId = null, ?string $search = null): array
    {
        $offset = ($page - 1) * $perPage;
        $whereConditions = [];
        $params = [];
        

        $whereConditions[] = "C.category_read_level >= ?";
        $params[] = $userLevel;

        if($userLevel > 1)
        {
            $whereConditions[] = "P.posting_state = ?";
            $params[] = 0;
        }

        if ($categoryId !== null && $categoryId > 0) {
            $whereConditions[] = "P.category_index = ?";
            $params[] = $categoryId;
        }

        if ($search) {
            $whereConditions[] = "P.posting_title LIKE ?";
            $params[] = "%{$search}%";
        }

        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

        $sql = "SELECT P.*, C.category_name, U.user_id as user_name 
                FROM posting_list P 
                LEFT JOIN category_list C ON P.category_index = C.category_index 
                LEFT JOIN user_list U ON P.user_index = U.user_index 
                {$whereClause} 
                ORDER BY P.posting_index DESC 
                LIMIT ? OFFSET ?";

        $params[] = $perPage;
        $params[] = $offset;

        return $this->db->fetchAll($sql, $params);
    }

    public function getById(int $userLevel, int $postId): ?array
    {
        $sql = "SELECT P.*, C.category_name, U.user_id as user_name 
                FROM posting_list P 
                LEFT JOIN category_list C ON P.category_index = C.category_index 
                LEFT JOIN user_list U ON P.user_index = U.user_index 
                WHERE C.category_read_level >= ? AND P.posting_index = ?" ;
        
        return $this->db->fetch($sql, [$userLevel, $postId]);
    }

    public function create(array $data): int
    {
        $sql = "SELECT COUNT(*) as count FROM category_list WHERE category_read_level >= ? AND category_index = ?";
        $result = $this->db->fetch($sql, [$data['user_lavel'], $data['category_index']]);
        if ($result == null || $result['count'] < 1)
            throw new \Exception("해당 카테고리에 글을 작성할 권한이 없습니다.");

        $sql = "INSERT INTO posting_list (posting_title, posting_content, category_index, user_index, posting_first_post_datetime) 
                VALUES (?, ?, ?, ?, NOW())";
        
        $this->db->query($sql, [
            $data['title'],
            $data['content'],
            $data['category_index'],
            $data['user_index']
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(int $postId, array $data): bool
    {
        $sql = "UPDATE posting_list 
                SET posting_title = ?, posting_content = ?, category_index = ?, posting_last_edit_datetime = NOW() 
                WHERE posting_index = ?";
        
        $stmt = $this->db->query($sql, [
            $data['title'],
            $data['content'],
            $data['category_index'],
            $postId
        ]);

        return $stmt->rowCount() > 0;
    }

    public function disable(int $postId): bool
    {
        $sql = "UPDATE posting_list SET posting_state = 1 WHERE posting_index = ?";
        $stmt = $this->db->query($sql, [$postId]);
        return $stmt->rowCount() > 0;
    }

    public function getTotalCount(?int $categoryId = null, ?string $search = null): int
    {
        $whereConditions = [];
        $params = [];

        if ($categoryId !== null && $categoryId > 0) {
            $whereConditions[] = "category_index = ?";
            $params[] = $categoryId;
        }

        if ($search) {
            $whereConditions[] = "posting_title LIKE ?";
            $params[] = "%{$search}%";
        }

        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

        $sql = "SELECT COUNT(*) as count FROM posting_list {$whereClause}";
        $result = $this->db->fetch($sql, $params);
        
        return $result ? (int)$result['count'] : 0;
    }

    public function getByUserId(int $userId): array
    {
        $sql = "SELECT P.*, C.category_name 
                FROM posting_list P 
                LEFT JOIN category_list C ON P.category_index = C.category_index 
                WHERE P.user_index = ? 
                ORDER BY P.posting_index DESC";
        
        return $this->db->fetchAll($sql, [$userId]);
    }

    public function incrementReadCount(int $postId): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $today = date("Y-m-d");
        $sessionKey = 'post_read_' . $postId . '_' . $today;
        
        // 오래된 날짜의 세션 키들 정리
        if (isset($_SESSION)) {
            foreach ($_SESSION as $key => $value) {
                if (strpos($key, 'post_read_' . $postId . '_') === 0) {
                    $sessionDate = substr($key, strlen('post_read_' . $postId . '_'));
                    if ($sessionDate !== $today) {
                        unset($_SESSION[$key]);
                    }
                }
            }
        }
        
        // 이미 오늘 조회한 포스트인지 확인
        if (isset($_SESSION[$sessionKey])) {
            return false;
        }
        
        // 조회수 증가
        $sql = "UPDATE posting_list SET posting_read_cnt = posting_read_cnt + 1 WHERE posting_index = ?";
        $this->db->query($sql, [$postId]);
        
        // 세션에 조회 완료 표시
        $_SESSION[$sessionKey] = true;
        
        return true;
    }
}
