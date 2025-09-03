<?php

namespace Blog\Controllers;

use Blog\Models\Post;
use Blog\Models\Category;

class PostController extends BaseController
{
    private $postModel;
    private $categoryModel;

    public function __construct()
    {
        parent::__construct();
        $this->postModel = new Post();
        $this->categoryModel = new Category();
    }

    public function show(int $postId = null): void
    {
        // 쿼리스트링 fallback 처리
        if ($postId === null) {
            $postId = (int)$this->getParam('posting_index', -1);
        }
        if ($postId <= 0) {
            $this->session->setFlash('error', '잘못된 접근입니다.');
            $this->redirect('/index.php');
            return;
        }

        $post = $this->postModel->getById($postId);
        
        if (!$post) {
            $this->session->setFlash('error', '게시글을 찾을 수 없습니다.');
            $this->redirect('/index.php');
        }

        // 조회수 증가
        $this->postModel->incrementReadCount($postId);

        $categories = $this->categoryModel->getAll();
        
        $this->renderLayout('main', 'posts/show', [
            'post' => $post,
            'categories' => $categories,
            'csrfToken' => $this->view->csrfToken()
        ]);
    }

    public function createForm(): void
    {
        $this->auth->requireWritePermission();
        
        $categoryId = (int)$this->getParam('category_index', -1);
        $categories = $this->categoryModel->getAll();
        
        $this->renderLayout('main', 'posts/create', [
            'categories' => $categories,
            'selectedCategory' => $categoryId > 0 ? $categoryId : null,
            'csrfToken' => $this->view->csrfToken()
        ]);
    }

    public function create(): void
    {
        $this->auth->requireWritePermission();

        if (!$this->isPost()) {
            $this->redirect('/writer.php');
        }

        if (!$this->validateCsrfToken()) {
            $this->session->setFlash('error', '보안 토큰이 유효하지 않습니다.');
            $this->redirect('/writer.php');
        }

        $title = $this->sanitizeInput($this->getParam('title', ''));
        $content = $this->getParam('content', '');
        $categoryId = (int)$this->getParam('category_index', -1);

        $errors = $this->validateRequired([
            'title' => $title,
            'content' => $content,
            'category_index' => $categoryId
        ], ['title', 'content', 'category_index']);

        if (!empty($errors)) {
            $this->session->setFlash('error', '모든 필드를 입력해주세요.');
            $this->redirect('/writer.php');
        }

        if ($categoryId <= 0) {
            $this->session->setFlash('error', '카테고리를 선택해주세요.');
            $this->redirect('/writer.php');
        }

        $userIndex = $this->auth->getCurrentUserIndex();
        
        try {
            $postId = $this->postModel->create([
                'title' => $title,
                'content' => $content,
                'category_index' => $categoryId,
                'user_index' => $userIndex
            ]);

            $this->session->setFlash('success', '게시글이 작성되었습니다.');
            $this->redirect("/reader.php?posting_index={$postId}");
        } catch (\Exception $e) {
            $this->session->setFlash('error', '게시글 작성 중 오류가 발생했습니다.');
            $this->redirect('/writer.php');
        }
    }

    public function editForm(int $postId = null): void
    {
        $this->auth->requireLogin();

        if ($postId === null) {
            $postId = (int)$this->getParam('posting_index', -1);
        }
        if ($postId <= 0) {
            $this->session->setFlash('error', '잘못된 접근입니다.');
            $this->redirect('/index.php');
            return;
        }
        
        $post = $this->postModel->getById($postId);
        
        if (!$post) {
            $this->session->setFlash('error', '게시글을 찾을 수 없습니다.');
            $this->redirect('/index.php');
        }

        $currentUserIndex = $this->auth->getCurrentUserIndex();
        if ($post['user_index'] !== $currentUserIndex) {
            $this->session->setFlash('error', '수정 권한이 없습니다.');
            $this->redirect('/index.php');
        }

        $categories = $this->categoryModel->getAll();
        
        $this->renderLayout('main', 'posts/edit', [
            'post' => $post,
            'categories' => $categories,
            'csrfToken' => $this->view->csrfToken()
        ]);
    }

    public function update(int $postId): void
    {
        $this->auth->requireLogin();

        if (!$this->isPost()) {
            $this->redirect("/post/edit/{$postId}");
        }

        if (!$this->validateCsrfToken()) {
            $this->session->setFlash('error', '보안 토큰이 유효하지 않습니다.');
            $this->redirect("/post/edit/{$postId}");
        }

        $post = $this->postModel->getById($postId);
        
        if (!$post) {
            $this->session->setFlash('error', '게시글을 찾을 수 없습니다.');
            $this->redirect('/index.php');
        }

        $currentUserIndex = $this->auth->getCurrentUserIndex();
        if ($post['user_index'] !== $currentUserIndex) {
            $this->session->setFlash('error', '수정 권한이 없습니다.');
            $this->redirect('/index.php');
        }

        $title = $this->sanitizeInput($this->getParam('title', ''));
        $content = $this->getParam('content', '');
        $categoryId = (int)$this->getParam('category_index', -1);

        $errors = $this->validateRequired([
            'title' => $title,
            'content' => $content,
            'category_index' => $categoryId
        ], ['title', 'content', 'category_index']);

        if (!empty($errors)) {
            $this->session->setFlash('error', '모든 필드를 입력해주세요.');
            $this->redirect("/post/edit/{$postId}");
        }

        if ($categoryId <= 0) {
            $this->session->setFlash('error', '카테고리를 선택해주세요.');
            $this->redirect("/post/edit/{$postId}");
        }

        try {
            $this->postModel->update($postId, [
                'title' => $title,
                'content' => $content,
                'category_index' => $categoryId
            ]);

            $this->session->setFlash('success', '게시글이 수정되었습니다.');
            $this->redirect("/reader.php?posting_index={$postId}");
        } catch (\Exception $e) {
            $this->session->setFlash('error', '게시글 수정 중 오류가 발생했습니다.');
            $this->redirect("/post/edit/{$postId}");
        }
    }

    public function delete(int $postId): void
    {
        $this->auth->requireLogin();

        if (!$this->isPost()) {
            $this->redirect('/index.php');
        }

        if (!$this->validateCsrfToken()) {
            $this->session->setFlash('error', '보안 토큰이 유효하지 않습니다.');
            $this->redirect('/index.php');
        }

        $post = $this->postModel->getById($postId);
        
        if (!$post) {
            $this->session->setFlash('error', '게시글을 찾을 수 없습니다.');
            $this->redirect('/index.php');
        }

        $currentUserIndex = $this->auth->getCurrentUserIndex();
        if ($post['user_index'] !== $currentUserIndex) {
            $this->session->setFlash('error', '삭제 권한이 없습니다.');
            $this->redirect('/index.php');
        }

        try {
            $this->postModel->delete($postId);
            $this->session->setFlash('success', '게시글이 삭제되었습니다.');
        } catch (\Exception $e) {
            $this->session->setFlash('error', '게시글 삭제 중 오류가 발생했습니다.');
        }

        $this->redirect('/index.php');
    }
}
