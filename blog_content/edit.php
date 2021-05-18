<!-- content/edit.php -->
<!doctype html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Blog</title>
    <link rel="stylesheet" href="/css/after.css">
    <link rel="stylesheet" href="/css/blog/main_outer.css">
    <link rel="stylesheet" href="/css/blog/main_header.css">
    <link rel="stylesheet" href="/css/blog/main_aside.css">
    <link rel="stylesheet" href="/css/blog/main_writeContent.css">
    <link rel="stylesheet" href="/css/blog/main_footer.css">

    <?php
        include '../php/blog.php';
        $user = checkUser($_POST['id'], $_POST['pw']);
        $content = getEditContent($user['user_index'], $_POST['content_index']);
    ?>

    <script type="text/javascript" src="/smarteditor2/js/HuskyEZCreator.js" charset="utf-8"></script>
    <script src="/js/blog.js"> </script>
    <script>
        var submitLeave = false;

        window.onload = function() {
            <?php echoMainOnload($user['user_index']) ?>

            if(<?php echo $content == null ? -1 : 0; ?> < 0){
                alert('잘못된 접근');
                blogClick();
            }
            else if(<?php if(!checkUserCanWrite($user)) echo 'true'; else echo 'false'; ?>){
                alert('하루 글쓰기 수가 초과 되었습니다');
                blogClick();
            }
            else{
                if(sessionStorage.getItem('title') !== null)
                    document.getElementById("input_title").value = sessionStorage.getItem('title');
                else
                    document.getElementById("input_title").value = <?php echo "'".addslashes($content['title'])."';\n"; ?>

                if(sessionStorage.getItem('content') !== null)
                    document.getElementById("input_content").value = sessionStorage.getItem('content');
                else
                    document.getElementById("input_content").value = <?php echo "'".addslashes($content['content'])."';\n"; ?>
            }
        }
        window.onbeforeunload = function(){
            if(submitLeave == false){
                sessionStorage.removeItem('title');
                sessionStorage.removeItem('content');
            }
        }

        function onInput(input, max_length){
            if(input.value.length > max_length){
                alert('최대 문자열 길이는 ' + max_length + 'byte 입니다.')
                return input.value = input.value.substring(0, max_length);
            }
        }
        function submitClick() {
            if(document.getElementById("input_title").value == ''){
                alert('제목을 작성해주세요.')
                return;
            }
            else if(document.getElementById("input_content").value == '<p>&nbsp;</p>'){
                alert('내용을 작성해주세요.')
                return;
            }

            submitLeave = true;
            oEditors.getById["input_content"].exec("UPDATE_CONTENTS_FIELD", []);

            var form = getDefaultPostForm('editCheck');
            var editorStr = document.getElementById("input_content").value;
            editorStr = editorStr.replaceAll('<div', '<p');
            editorStr = editorStr.replaceAll('</div>', '</p>');

            var editorFrame = document.getElementById('editor_frame');
            var inputFrame = editorFrame.contentWindow.document.getElementById('se2_iframe');
            var imgClass = inputFrame.contentWindow.document.getElementsByClassName('photo');

            if(imgClass.length > 0){
                var path = imgClass[0].src.split('/res/');
                var hiddenField = document.createElement('input');
                hiddenField.setAttribute('type', 'hidden');
                hiddenField.setAttribute('name', 'thumbnail');
                hiddenField.setAttribute('value', '/res/' + path[1]);
                form.appendChild(hiddenField);
            }

            sessionStorage.setItem('title', document.getElementById("input_title").value);
            var hiddenField = document.createElement('input');
            hiddenField.setAttribute('type', 'hidden');
            hiddenField.setAttribute('name', 'content_index');
            hiddenField.setAttribute('value', <?php echo $_POST['content_index']; ?>);
            form.appendChild(hiddenField);

            sessionStorage.setItem('title', document.getElementById("input_title").value);
            var hiddenField = document.createElement('input');
            hiddenField.setAttribute('type', 'hidden');
            hiddenField.setAttribute('name', 'title');
            hiddenField.setAttribute('value', document.getElementById("input_title").value);
            form.appendChild(hiddenField);

            var inputArea = inputFrame.contentWindow.document.getElementsByClassName('se2_inputarea')[0];
            var summaryStr = inputArea.innerText.substring(0, 200);
            if(inputArea.innerText.length > 200) summaryStr += '...';
            summaryStr  = summaryStr.replaceAll('\n', ' ');
            var hiddenField = document.createElement('input');
            hiddenField.setAttribute('type', 'hidden');
            hiddenField.setAttribute('name', 'summary');
            hiddenField.setAttribute('value', summaryStr);
            form.appendChild(hiddenField);

            sessionStorage.setItem('content', editorStr);
            var hiddenField = document.createElement('input');
            hiddenField.setAttribute('type', 'hidden');
            hiddenField.setAttribute('name', 'content');
            hiddenField.setAttribute('value', editorStr);
            form.appendChild(hiddenField);

            document.body.appendChild(form);
            form.submit();
        }
    </script>
</head>
<body>
    <div id="main">
        <header> <?php echoHeader($user['user_index'], $user['level']); ?> </header>
        <section>
            <div id=content>
                <input id=input_title type='text' placeholder='제목' oninput='onInput(this, 50)' value='<?php echo $content['title'] ?>'/>
                <textarea id=input_content name=input_content style="width:100%; height:600px; min-width:600px; min-height:600px; display:none;"></textarea>
                <button id='btn_submit' onclick=submitClick()>수정</button>
            </div>
        </section>
        <footer>
            <p>Contact : bae4969@naver.com</br>
            Github : <a class=footer href=https://github.com/bae4969>https://github.com/bae4969</a></p>
        </footer>
    </div>
    <script type="text/javascript">
        var oEditors = [];
        nhn.husky.EZCreator.createInIFrame({
            oAppRef: oEditors,
            elPlaceHolder: "input_content",
            sSkinURI: "/smarteditor2/SmartEditor2Skin.html",
            htParams : {
		        bUseVerticalResizer : false,
                bUseModeChanger : false,
            },
            fCreator: "createSEditor2"
        });
    </script>
</body>
</html>
