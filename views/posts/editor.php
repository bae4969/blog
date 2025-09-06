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
                    <div id="quill-editor" class="quill-container"></div>
                    <textarea id="input_content" name="content" style="display: none;"><?= $isEdit ? $post['posting_content'] : '' ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button id="btn_submit" type="submit" class="btn btn-primary">
                        <span class="btn-text"><?= $isEdit ? '수정' : '작성' ?></span>
                        <span class="btn-loading" style="display: none;"><?= $isEdit ? '수정 중...' : '작성 중...' ?></span>
                    </button>
                    <button type="button" onclick="handleCancel()" class="btn btn-secondary">취소</button>
                </div>
            </form>
        </div>
    </article>
</div>

<!-- Quill.js 로컬 파일 -->
<link href="/vendor/quill/quill.snow.css" rel="stylesheet">
<script src="/vendor/quill/quill.min.js"></script>

<script>
// Quill 에디터 초기화
let quill;
let hasUnsavedChanges = false;
let initialContent = '';

document.addEventListener('DOMContentLoaded', function() {
    // 다크 테마에 맞는 Quill 설정
    quill = new Quill('#quill-editor', {
        theme: 'snow',
        placeholder: '내용을 입력하세요...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link', 'image'],
                ['blockquote', 'code-block'],
                ['clean']
            ]
        }
    });

    // 기존 내용이 있으면 에디터에 로드
    const existingContent = document.getElementById('input_content').value;
    if (existingContent) {
        quill.root.innerHTML = existingContent;
    }
    
    // 초기 내용 저장
    initialContent = quill.root.innerHTML;

    // Quill 내용 변경 시 hidden textarea 업데이트 및 변경 상태 추적
    quill.on('text-change', function() {
        document.getElementById('input_content').value = quill.root.innerHTML;
        validateContent();
        
        // 내용이 변경되었는지 확인
        const currentContent = quill.root.innerHTML;
        hasUnsavedChanges = (currentContent !== initialContent || 
                           document.getElementById('input_title').value.trim() !== '' ||
                           document.getElementById('input_category').value !== '-1');
    });

    // 제목과 카테고리 변경도 감지
    document.getElementById('input_title').addEventListener('input', function() {
        const currentContent = quill.root.innerHTML;
        hasUnsavedChanges = (currentContent !== initialContent || 
                           this.value.trim() !== '' ||
                           document.getElementById('input_category').value !== '-1');
    });
    
    document.getElementById('input_category').addEventListener('change', function() {
        const currentContent = quill.root.innerHTML;
        hasUnsavedChanges = (currentContent !== initialContent || 
                           document.getElementById('input_title').value.trim() !== '' ||
                           this.value !== '-1');
    });

    // 초기 유효성 검사
    validateTitle();
    validateCategory();
    validateContent();
    
    // 제목 아이콘 클릭 시 확인 메시지
    const mainTitle = document.getElementById('mainTitle');
    if (mainTitle) {
        mainTitle.addEventListener('click', function(e) {
            if (hasUnsavedChanges) {
                e.preventDefault();
                if (confirm('작성 중인 내용이 있습니다. 정말 나가시겠습니까?')) {
                    hasUnsavedChanges = false;
                    location.href = 'index.php';
                }
            }
        });
    }
    
    // 다른 링크들 클릭 시 확인 메시지
    const topLeft = document.getElementById('topLeft');
    const topRight = document.getElementById('topRight');
    const topWrite = document.getElementById('topWrite');
    
    [topLeft, topRight, topWrite].forEach(element => {
        if (element) {
            element.addEventListener('click', function(e) {
                if (hasUnsavedChanges) {
                    e.preventDefault();
                    if (confirm('작성 중인 내용이 있습니다. 정말 나가시겠습니까?')) {
                        hasUnsavedChanges = false;
                        // 원래 동작 실행
                        if (element === topLeft) {
                            location.href = 'index.php';
                        } else if (element === topRight) {
                            loginoutClick();
                        } else if (element === topWrite) {
                            writePostingClick();
                        }
                    }
                }
            });
        }
    });
});

// 페이지 이탈 시 확인 메시지 (브라우저 뒤로가기, 새로고침, 탭 닫기 등)
window.addEventListener('beforeunload', function(e) {
    if (hasUnsavedChanges) {
        e.preventDefault();
        e.returnValue = ''; // 최신 브라우저에서는 빈 문자열만 설정하면 됨
        return '';
    }
});

// 폼 제출 이벤트 처리
document.querySelector('.post-form').addEventListener('submit', function(e) {
    e.preventDefault();
    hasUnsavedChanges = false; // 제출 시 변경 상태 초기화
    handleFormSubmit();
});

// 실시간 유효성 검사
document.getElementById('input_title').addEventListener('input', validateTitle);
document.getElementById('input_category').addEventListener('change', validateCategory);

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

// 내용 유효성 검사 (Quill 에디터용)
function validateContent() {
    const contentTextarea = document.getElementById('input_content');
    const content = contentTextarea.value.trim();
    
    // Quill 에디터의 실제 텍스트 내용 확인
    const textContent = quill ? quill.getText().trim() : '';
    
    if (textContent.length === 0) {
        showFieldError(document.querySelector('.quill-container'), '내용을 입력해주세요.');
        return false;
    } else if (textContent.length < 10) {
        showFieldError(document.querySelector('.quill-container'), '내용은 최소 10자 이상 입력해주세요.');
        return false;
    } else {
        clearFieldError(document.querySelector('.quill-container'));
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

// 취소 버튼 처리
function handleCancel() {
    if (hasUnsavedChanges) {
        if (confirm('작성 중인 내용이 있습니다. 정말 나가시겠습니까?')) {
            hasUnsavedChanges = false; // 확인 시 변경 상태 초기화
            <?php if ($isEdit): ?>
                location.href = '/reader.php?posting_index=<?= $post['posting_index'] ?>';
            <?php else: ?>
                location.href = '/index.php';
            <?php endif; ?>
        }
    } else {
        <?php if ($isEdit): ?>
            location.href = '/reader.php?posting_index=<?= $post['posting_index'] ?>';
        <?php else: ?>
            location.href = '/index.php';
        <?php endif; ?>
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

// Quill 에디터 다크 테마 스타일링
const quillDarkTheme = `
    <style>
    .ql-toolbar {
        background-color: #222426 !important;
        border-color: #36383A !important;
        color: #C3C3C3 !important;
    }
    
    .ql-toolbar .ql-stroke {
        stroke: #C3C3C3 !important;
    }
    
    .ql-toolbar .ql-fill {
        fill: #C3C3C3 !important;
    }
    
    .ql-toolbar button:hover {
        background-color: #36383A !important;
    }
    
    .ql-toolbar button.ql-active {
        background-color: #36383A !important;
        color: white !important;
    }
    
    .ql-container {
        background-color: #1A1C1D !important;
        color: #C3C3C3 !important;
        border-color: #36383A !important;
    }
    
    .ql-editor {
        background-color: #1A1C1D !important;
        color: #C3C3C3 !important;
        min-height: 300px !important;
    }
    
    .ql-editor.ql-blank::before {
        color: #666 !important;
    }
    
    .quill-container {
        border-radius: 8px;
        overflow: hidden;
    }
    
    .quill-container.error {
        border: 2px solid #dc3545;
    }
    
    .field-error {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 5px;
    }
    </style>
`;

// 다크 테마 스타일 추가
document.head.insertAdjacentHTML('beforeend', quillDarkTheme);
</script>
