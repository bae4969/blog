<?php

namespace Blog\Core;

use Blog\Models\User;

class Auth
{
    private $session;
    private $userModel;

    public function __construct()
    {
        $this->session = new Session();
        $this->userModel = new User();
    }

    public function login(string $userId, string $password): bool
    {
        $user = $this->userModel->authenticate($userId, $password);
        
        if ($user) {
            $this->session->regenerate();
            $this->session->set('user_index', $user['user_index']);
            $this->session->set('user_id', $user['user_id']);
            $this->session->set('user_level', $user['user_level']);
            $this->session->set('user_state', $user['user_state']);
            return true;
        }
        
        return false;
    }

    public function logout(): void
    {
        $this->session->destroy();
    }

    public function isLoggedIn(): bool
    {
        return $this->session->has('user_index');
    }

    public function getCurrentUser(): ?array
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        $userId = $this->session->get('user_id');
        return $this->userModel->getUserById($userId);
    }

    public function getCurrentUserId(): ?string
    {
        return $this->session->get('user_id');
    }

    public function getCurrentUserIndex(): ?int
    {
        return $this->session->get('user_index');
    }

    public function getCurrentUserName(): ?string
    {
        return $this->session->get('user_id');
    }

    public function canWrite(): bool
    {
        if (!$this->isLoggedIn()) {
            return false;
        }

        $userIndex = $this->getCurrentUserIndex();
        return $this->userModel->canWrite($userIndex);
    }

    public function requireLogin(): void
    {
        if (!$this->isLoggedIn()) {
            header('Location: /login.php');
            exit;
        }
    }

    public function requireWritePermission(): void
    {
        $this->requireLogin();
        
        if (!$this->canWrite()) {
            $this->session->setFlash('error', '글쓰기 횟수가 초과되었습니다.');
            header('Location: /index.php');
            exit;
        }
    }
}
