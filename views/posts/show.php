<div id="post-detail" class="post-wrapper">
    <article class="post">
        <header class="post-header">
            <h1 class="post-title"><?= $view->escape($post['posting_title']) ?></h1>
            <div class="post-meta">
                <span class="post-category"><?= $view->escape($post['category_name'] ?? '미분류') ?></span>
                <span class="post-author"><?= $view->escape($post['user_name'] ?? '익명') ?></span>
                <span class="post-date"><?= date('Y-m-d H:i', strtotime($post['posting_first_post_datetime'])) ?></span>
                <?php if ($post['posting_last_edit_datetime'] && $post['posting_last_edit_datetime'] !== $post['posting_first_post_datetime']): ?>
                    <span class="post-updated">(수정: <?= date('Y-m-d H:i', strtotime($post['posting_last_edit_datetime'])) ?>)</span>
                <?php endif; ?>
                <span class="post-read-count">조회: <?= $post['posting_read_cnt'] ?></span>
            </div>
        </header>
        
        <div class="post-content">
            <?= $post['posting_content'] ?>
        </div>
        
        <?php if ($auth->isLoggedIn() && $auth->getCurrentUserIndex() === $post['user_index']): ?>
            <div class="post-actions">
                <a href="/post/edit/<?= $post['posting_index'] ?>" class="btn btn-edit">수정</a>
                <button onclick="disablePost(<?= $post['posting_index'] ?>)" class="btn btn-disable">삭제</button>
            </div>
        <?php endif; ?>
    </article>
</div>

<form id="disable-form" method="POST" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
</form>

<script>
function disablePost(postId) {
    if (confirm('정말로 이 게시글을 삭제하시겠습니까?')) {
        const form = document.getElementById('disable-form');
        form.action = '/post/disable/' + postId;
        form.submit();
    }
}

function loginoutClick() {
    <?php if ($auth->isLoggedIn()): ?>
        if (confirm('로그아웃하시겠습니까?')) {
            location.href = '/logout.php';
        }
    <?php else: ?>
        location.href = '/login.php';
    <?php endif; ?>
}

function writePostingClick() {
    location.href = '/writer.php?category_index=<?= $post['category_index'] ?? -1 ?>';
}
</script>
