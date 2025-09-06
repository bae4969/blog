<div id="post-writer" class="post-wrapper">
    <article class="post">
        <div class="post-content">
            <form method="POST" action="<?= $isEdit ? '/post/update/' . $post['posting_index'] : '/writer.php' ?>" class="post-form" novalidate>
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                
                <div class="form-row">
                    <div class="form-group form-group-category">
                        <label for="input_category" class="form-label">카테고리</label>
                        <select id="input_category" name="category_index" required class="form-control">
                            <option value="-1">카테고리를 선택하세요</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['category_index'] ?>" 
                                        <?= ($isEdit ? $post['category_index'] : $selectedCategory) == $category['category_index'] ? 'selected' : '' ?>>
                                    <?= $view->escape($category['category_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group form-group-title">
                        <label for="input_title" class="form-label">제목</label>
                        <input id="input_title" name="title" type="text" placeholder="제목을 입력하세요 (최대 255자)" 
                               maxlength="255" required class="form-control" 
                               value="<?= $isEdit ? $view->escape($post['posting_title']) : '' ?>" />
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="input_content" class="form-label">내용</label>
                    <textarea id="input_content" name="content" rows="15" 
                              class="form-control textarea-content" 
                              placeholder="내용을 입력하세요..."><?= $isEdit ? $post['posting_content'] : '' ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button id="btn_submit" type="submit" class="btn btn-primary">
                        <span class="btn-text"><?= $isEdit ? '수정' : '작성' ?></span>
                        <span class="btn-loading" style="display: none;"><?= $isEdit ? '수정 중...' : '작성 중...' ?></span>
                    </button>
                    <button type="button" onclick="location.href='<?= $isEdit ? '/reader.php?posting_index=' . $post['posting_index'] : '/index.php' ?>'" class="btn btn-secondary">취소</button>
                </div>
            </form>
        </div>
    </article>
</div>

<script>
// 폼 제출 이벤트 처리
document.querySelector('.post-form').addEventListener('submit', function(e) {
    e.preventDefault();
    handleFormSubmit();
});

// 실시간 유효성 검사
document.getElementById('input_title').addEventListener('input', validateTitle);
document.getElementById('input_category').addEventListener('change', validateCategory);
document.getElementById('input_content').addEventListener('input', validateContent);

// 제목 유효성 검사
function validateTitle() {
    const titleInput = document.getElementById('input_title');
    const title = titleInput.value.trim();
    
    if (title.length === 0) {
        showFieldError(titleInput, '제목을 입력해주세요.');
        return false;
    } else if (title.length > 255) {
        showFieldError(titleInput, '제목은 255자를 초과할 수 없습니다.');
        return false;
    } else {
        clearFieldError(titleInput);
        return true;
    }
}

// 카테고리 유효성 검사
function validateCategory() {
    const categorySelect = document.getElementById('input_category');
    const category = categorySelect.value;
    
    if (category === '-1') {
        showFieldError(categorySelect, '카테고리를 선택해주세요.');
        return false;
    } else {
        clearFieldError(categorySelect);
        return true;
    }
}

// 내용 유효성 검사
function validateContent() {
    const contentTextarea = document.getElementById('input_content');
    const content = contentTextarea.value.trim();
    
    if (content.length === 0) {
        showFieldError(contentTextarea, '내용을 입력해주세요.');
        return false;
    } else if (content.length < 10) {
        showFieldError(contentTextarea, '내용은 최소 10자 이상 입력해주세요.');
        return false;
    } else {
        clearFieldError(contentTextarea);
        return true;
    }
}

// 필드 에러 표시
function showFieldError(field, message) {
    clearFieldError(field);
    field.classList.add('error');
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    
    field.parentNode.appendChild(errorDiv);
}

// 필드 에러 제거
function clearFieldError(field) {
    field.classList.remove('error');
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
}

// 폼 제출 처리
function handleFormSubmit() {
    const isTitleValid = validateTitle();
    const isCategoryValid = validateCategory();
    const isContentValid = validateContent();
    
    if (!isTitleValid || !isCategoryValid || !isContentValid) {
        showNotification('입력 정보를 확인해주세요.', 'error');
        return;
    }
    
    // 로딩 상태 표시
    const submitBtn = document.getElementById('btn_submit');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoading = submitBtn.querySelector('.btn-loading');
    
    btnText.style.display = 'none';
    btnLoading.style.display = 'inline';
    submitBtn.disabled = true;
    
    // 폼 제출
    document.querySelector('.post-form').submit();
}

// 알림 표시
function showNotification(message, type = 'info') {
    // 기존 알림 제거
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // 3초 후 자동 제거
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// 공통 함수들
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
    location.href = '/writer.php<?= ($isEdit ? '?category_index=' . $post['category_index'] : ($selectedCategory ? '?category_index=' . $selectedCategory : '')) ?>';
}

// 페이지 로드 시 초기 유효성 검사
document.addEventListener('DOMContentLoaded', function() {
    // 빈 상태로 초기화
    validateTitle();
    validateCategory();
    validateContent();
});
</script>
