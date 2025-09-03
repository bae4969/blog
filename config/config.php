<?php

return [
    'app_name' => 'Developer Blog',
    'app_url' => 'https://baenoipddnsaddress.ddns.net:40000',
    'timezone' => 'Asia/Seoul',
    'session_lifetime' => 3600,
    'csrf_token_name' => 'csrf_token',
    'upload_path' => __DIR__ . '/../public/uploads',
    'max_file_size' => 5 * 1024 * 1024, // 5MB
    'allowed_file_types' => ['jpg', 'jpeg', 'png', 'gif'],
    'posts_per_page' => 10,
    'contact_email' => 'bae4969@naver.com',
    'github_url' => 'https://github.com/bae4969'
];
