<div id="content">
    <form method="POST" action="/writer.php?posting_index=<?= $post['posting_index'] ?>" class="post-form" novalidate>
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        
        <div class="form-group form-row">
            <input id="input_title" name="title" type="text" placeholder="제목을 입력하세요 (최대 255자)" 
                   maxlength="255" required class="form-control form-control--lg" 
                   value="<?= $view->escape($post['posting_title']) ?>" />
        </div>
        
        <div class="form-group form-row">
            <select id="input_category" name="category_index" required class="form-control form-control--select">
                <option value="-1">카테고리 선택</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['category_index'] ?>" 
                            <?= $post['category_index'] == $category['category_index'] ? 'selected' : '' ?>>
                        <?= $view->escape($category['category_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <textarea id="input_content" name="content" rows="15" style="width:100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: inherit; font-size: 14px; line-height: 1.5;" placeholder="내용을 입력하세요..."><?= $post['posting_content'] ?></textarea>
        </div>
        
        <div class="form-group">
            <button id="btn_submit" type="submit" class="btn btn-primary">수정</button>
            <a href="/reader.php?posting_index=<?= $post['posting_index'] ?>" class="btn btn-secondary">취소</a>
        </div>
    </form>
</div>

<script>
document.querySelector('.post-form').addEventListener('submit', function(e) {
    e.preventDefault();
    submitClick();
});

function submitClick() {
    const title = document.getElementById("input_title").value.trim();
    const category = document.getElementById("input_category").value;
    const content = document.getElementById("input_content").value.trim();
    
    if (!title) {
        alert('제목을 입력해주세요.');
        return;
    }
    
    if (category === '-1') {
        alert('카테고리를 선택해주세요.');
        return;
    }
    
    if (!content) {
        alert('내용을 입력해주세요.');
        return;
    }

    document.querySelector('.post-form').submit();
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
