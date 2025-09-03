<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $config['app_name'] ?></title>
    <link rel="stylesheet" href="/css/main.css">
    <?php if (isset($additionalCss)): ?>
        <?php foreach ($additionalCss as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <div id="main">
        <header>
            <div id="topLeft" onclick="location.href='/index.php'">Home</div>
            <div id="topRight" onclick="loginoutClick()">
                <?= $auth->isLoggedIn() ? '로그아웃' : '로그인' ?>
            </div>
            <?php if ($auth->isLoggedIn() && $auth->canWrite()): ?>
                <div id="topWrite" onclick="writePostingClick()">글쓰기</div>
            <?php endif; ?>
            <div id="title">
                <img id="mainTitle" onclick="location.href='index.php'" src="/res/title.png" alt="Blog Page" />
            </div>
        </header>
        
        <section>
            <aside>
                <div id="profile">
                    <?php if ($auth->isLoggedIn()): ?>
                        <p>안녕하세요, <?= $view->escape($auth->getCurrentUserName()) ?>님!</p>
                    <?php else: ?>
                        <p>로그인해주세요</p>
                    <?php endif; ?>
                </div>
                <div id="user_count">방문자: <?= number_format($visitorCount ?? 0) ?></div>
                <ul id="category">
                    <li class="category" onclick="location.href='/index.php'">전체</li>
                    <?php if (isset($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <li class="category" onclick="location.href='/index.php?category_index=<?= $category['category_index'] ?>'">
                                <?= $view->escape($category['category_name']) ?>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
                <div id="search_posting_div">
                    <select id="search_category_list">
                        <option value="-1">분류 선택</option>
                        <?php if (isset($categories)): ?>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['category_index'] ?>">
                                    <?= $view->escape($category['category_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <button id="search_posting_btn" onclick="searchPostingClick()">검색</button>
                    <input id="search_posting_text" type="text" placeholder="제목" 
                           onkeyup="if(window.event.keyCode==13){searchPostingClick()}" />
                </div>
            </aside>
            
            <div id="content">
                <?php if ($session->hasFlash('success')): ?>
                    <div class="alert alert-success">
                        <?= $view->escape($session->getFlash('success')) ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($session->hasFlash('error')): ?>
                    <div class="alert alert-error">
                        <?= $view->escape($session->getFlash('error')) ?>
                    </div>
                <?php endif; ?>
                
                <?= $content ?>
            </div>
        </section>
        
        <footer>
            <p>
                Contact: <?= $view->escape($config['contact_email']) ?><br>
                Github: <a class="footer" href="<?= $view->escape($config['github_url']) ?>"><?= $view->escape($config['github_url']) ?></a>
            </p>
        </footer>
    </div>

    <script src="/js/main.js"></script>
    <?php if (isset($additionalJs)): ?>
        <?php foreach ($additionalJs as $js): ?>
            <script src="<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
