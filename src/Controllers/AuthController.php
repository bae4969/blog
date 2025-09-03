<?php

namespace Blog\Controllers;

class AuthController extends BaseController
{
    public function loginForm(): void
    {
        if ($this->auth->isLoggedIn()) {
            $this->redirect('/index.php');
        }
        
        $this->renderLayout('auth', 'auth/login', [
            'csrfToken' => $this->view->csrfToken()
        ]);
    }

    public function login(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/login.php');
        }

        if (!$this->validateCsrfToken()) {
            $this->session->setFlash('error', '보안 토큰이 유효하지 않습니다.');
            $this->redirect('/login.php');
        }

        $userId = $this->sanitizeInput($this->getParam('user_id', ''));
        $password = $this->getParam('user_pw', '');

        $errors = $this->validateRequired(['user_id' => $userId, 'user_pw' => $password], ['user_id', 'user_pw']);
        
        if (!empty($errors)) {
            $this->session->setFlash('error', '아이디와 비밀번호를 모두 입력해주세요.');
            $this->redirect('/login.php');
        }

        if ($this->auth->login($userId, $password)) {
            $this->redirect('/index.php');
        } else {
            $this->session->setFlash('error', '아이디 또는 비밀번호가 일치하지 않습니다.');
            $this->redirect('/login.php');
        }
    }

    public function logout(): void
    {
        $this->auth->logout();
        $this->session->setFlash('success', '로그아웃되었습니다.');
        $this->redirect('/index.php');
    }

    public function verify(): void
    {
        if ($this->auth->isLoggedIn()) {
            $user = $this->auth->getCurrentUser();
            $canWrite = $this->auth->canWrite();
            
            $this->json([
                'state' => 0,
                'can_write' => $canWrite ? 1 : 0,
                'user_name' => $user['user_id'] ?? ''
            ]);
        } else {
            $this->json([
                'state' => 1,
                'etc' => '로그인이 필요합니다.'
            ]);
        }
    }
}
