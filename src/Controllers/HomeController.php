<?php

namespace Blog\Controllers;

use Blog\Models\Post;
use Blog\Models\Category;
use Blog\Models\User;

class HomeController extends BaseController
{
    private $postModel;
    private $categoryModel;
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->postModel = new Post();
        $this->categoryModel = new Category();
        $this->userModel = new User();
    }

    public function index(): void
    {
        $page = (int)$this->getParam('page', 1);
        $categoryId = (int)$this->getParam('category_index', -1);
        $search = $this->getParam('search_string', '');
        $userLevel = $this->auth->getCurrentUserLevel();
        
        // 카테고리 ID가 -1이면 null로 설정
        $categoryId = $categoryId > 0 ? $categoryId : null;
        
        // 방문자 수 업데이트
        $this->userModel->updateVisitorCount($userLevel);
        
        // 데이터 조회
        $posts = $this->postModel->getAll($userLevel, $page, 10, $categoryId, $search);
        $categories = $this->categoryModel->getAll($userLevel);
        $totalCount = $this->postModel->getTotalCount($categoryId, $search);
        $visitorCount = $this->userModel->getVisitorCount();
        
        // 페이지네이션 계산
        $totalPages = ceil($totalCount / 10);
        
        $data = [
            'posts' => $posts,
            'categories' => $categories,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'currentCategory' => $categoryId,
            'search' => $search,
            'visitorCount' => $visitorCount,
            'csrfToken' => $this->view->csrfToken()
        ];
        
        $this->renderLayout('main', 'home/index', $data);
    }

    public function search(): void
    {
        $categoryId = (int)$this->getParam('category_index', -1);
        $search = $this->getParam('search_string', '');
        $userLevel = $this->auth->getCurrentUserLevel();
        
        $categoryId = $categoryId > 0 ? $categoryId : null;
        
        $posts = $this->postModel->getAll($userLevel, 1, 10, $categoryId, $search);
        $totalCount = $this->postModel->getTotalCount($categoryId, $search);
        
        $this->json([
            'success' => true,
            'posts' => $posts,
            'totalCount' => $totalCount
        ]);
    }
}
