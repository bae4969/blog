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
            <textarea id="input_content" name="content" style="width:100%">
<?= $post['posting_content'] ?>
            </textarea>
        </div>
        
        <div class="form-group">
            <button id="btn_submit" type="submit" class="btn btn-primary">수정</button>
            <a href="/reader.php?posting_index=<?= $post['posting_index'] ?>" class="btn btn-secondary">취소</a>
        </div>
    </form>
</div>

<script src="/smarteditor2/js/HuskyEZCreator.js" charset="utf-8"></script>
<script>
var oEditors = [];

function initSmartEditor() {
    if (window.nhn && nhn.husky && nhn.husky.EZCreator && document.getElementById('input_content')) {
        nhn.husky.EZCreator.createInIFrame({
            oAppRef: oEditors,
            elPlaceHolder: "input_content",
            sSkinURI: "/smarteditor2/SmartEditor2Skin.html",
            fCreator: "createSEditor2"
        });
    } else {
        setTimeout(initSmartEditor, 60);
    }
}

window.addEventListener('load', initSmartEditor);

document.querySelector('.post-form').addEventListener('submit', function(e) {
    e.preventDefault();
    submitClick();
});

function submitClick() {
    const title = document.getElementById("input_title").value.trim();
    const category = document.getElementById("input_category").value;
    const content = (oEditors[0] && oEditors.getById) ? oEditors.getById["input_content"].getIR() : document.getElementById('input_content').value;
    
    if (!title) {
        alert('제목을 입력해주세요.');
        return;
    }
    
    if (category === '-1') {
        alert('카테고리를 선택해주세요.');
        return;
    }
    
    if (!content || content === '<p><br></p>') {
        alert('내용을 입력해주세요.');
        return;
    }

    if (oEditors.getById) {
        oEditors.getById["input_content"].exec("UPDATE_CONTENTS_FIELD", []);
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
