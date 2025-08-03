<!-- content/writer.php -->
<!doctype html>
<html lang=ko>
<head>
    <meta charset='utf-8'>
    <title>Developer Blog</title>
    <link type="text/css" rel="stylesheet" href="css/writer.css">
</head>
<body>
    <div id="main">
        <header>
            <div id=topLeft OnClick='location.href="/index.php"'>Home</div>
            <div id=topRight onclick=loginoutClick()></div>
            <div id=topWrite onclick=writePostingClick()></div>
            <div id=title>
                <img id=mainTitle OnClick='location.href="index.php"' src="res/title.png" alt="Blog Page" />
            </div>
        </header>
        <section>
            <div id=content>
                <input id=input_title type='text' placeholder='제목 (최대 30자)' oninput='onInput(this, 255)'/>
                <select id=input_category>
                    <option value=-1>분류 선택</option>
                </select>
                <textarea id=input_content name=input_content style="width:100%"></textarea>
                <button id='btn_submit' onclick=submitClick()>제출</button>
            </div>
        </section>
        <footer>
            <p>Contact : bae4969@naver.com</br>
            Github : <a class=footer href=https://github.com/bae4969>https://github.com/bae4969</a></p>
        </footer>
    </div>

    <script type="text/javascript" src="/smarteditor2/js/HuskyEZCreator.js" charset="utf-8"></script>
    <script type="text/javascript" src="/js/basicFunc.js" charset="utf-8"></script>
    <script type="text/javascript">
        var user_info_row;
        var category_index = -1;
        var posting_index = -1;  // neg == new posting, else == edit posting
        var oEditors = [];
        
        window.onload = function() {
            var params = {};
            location.search.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(str, key, value) { params[key] = value; });
            if(params['category_index'])
                category_index = params['category_index'];
            if(params['posting_index'])
                posting_index = params['posting_index'];

            verifyLogin();

            if(posting_index >= 0)
                initLastPosting();
            else
                initSelectCategory();
        }
        function loginoutClick() {
            if (user_info_row['state'] == 0) {
                deleteCookie('user_id');
                deleteCookie('user_pw');
                alert("로그아웃");
                location.href = 'index.php';
            }
            else {
                location.href = '/login.php';
            }
        }
        function writePostingClick(){
            location.href="/writer.php?category_index=" + category_index;
        }

        function verifyLogin() {
            var xhr = new XMLHttpRequest();
            var url = '/get/login_verify';
            url += '?user_id=' + getCookie('user_id');
            url += '&user_pw=' + getCookie('user_pw');
            xhr.open('GET', url);
            xhr.onreadystatechange = function () {
                if (xhr.readyState != XMLHttpRequest.DONE) return;
                if (xhr.status != 200) {
                    alert('Server Error (' + xhr.status + ')');
                    return;
                }

                user_info_row = JSON.parse(this.responseText);
                if(user_info_row['can_write'] == 0) {
                    alert('글쓰기 횟수가 초과 되었습니다.');
                    location.href = 'index.php';
                    return;
                }
                if (user_info_row['state'] == 0) {
                    document.getElementById("topRight").innerHTML = "로그아웃";
                    document.getElementById("topWrite").innerHTML = "글쓰기";
                }
                else {
                    document.getElementById("topRight").innerHTML = "로그인";
                    if(document.getElementById("topWrite") !== null)
                        document.getElementById("topWrite").innerHTML = "";
                }
            };
            xhr.send();
        }

        function initSelectCategory() {
            var xhr = new XMLHttpRequest();
            var url = 'get/category_write_list';
            url += '?user_id=' + getCookie('user_id');
            url += '&user_pw=' + getCookie('user_pw');
            xhr.open('GET', url);
            xhr.onreadystatechange = function () {
                if (xhr.readyState != XMLHttpRequest.DONE) return;
                if (xhr.status != 200) {
                    alert('Server Error (' + xhr.status + ')');
                    return;
                }

                var category_list = JSON.parse(this.responseText);
                if (category_list['state'] != 0) {
                    alert('카테고리 초기화 오류 (' + category_list['state'] + ')');
                    return;
                }
                

                var input_category = document.getElementById('input_category');
                for(var i = 1; i < category_list['data'].length; i++){
                    var option = document.createElement('option');
                    input_category.appendChild(option);
                    option.value = category_list['data'][i]['category_index']
                    option.innerHTML = category_list['data'][i]['category_name'];
                    if(category_index >= 0 && option.value == category_index)
                        option.selected = true;
                }

                initEditArea()
            };
            xhr.send();
        }
        function initLastPosting() {
            var xhr = new XMLHttpRequest();
            var url = 'get/full_posting';
            url += '?user_id=' + getCookie('user_id');
            url += '&user_pw=' + getCookie('user_pw');
            url += '&posting_index=' + posting_index;
            xhr.open('GET', url);
            xhr.onreadystatechange = function () {
                if (xhr.readyState != XMLHttpRequest.DONE) return;
                if (xhr.status != 200) {
                    alert('Server Error (' + xhr.status + ')');
                    return;
                }

                var full_posting = JSON.parse(this.responseText);
                if(full_posting['state'] != 0){
                    alert('포스팅 초기화 오류 (' + full_posting['state'] + ')');
                    location.href = 'reader.php?posting_index=' + posting_index;
                    return;
                }
                
                document.getElementById("input_category").style.display = "none";
                document.getElementById("input_title").value = full_posting['data']['posting_title'];
                document.getElementById("input_content").value = full_posting['data']['posting_content'];

                initEditArea()
            };
            xhr.send();
        }
        function initEditArea(){
            nhn.husky.EZCreator.createInIFrame({
                oAppRef: oEditors,
                elPlaceHolder: "input_content",
                sSkinURI: "/smarteditor2/SmartEditor2Skin.html",
                htParams : {
                    bUseVerticalResizer : false,
                    bUseModeChanger : false,
                },
                fCreator: "createSEditor2",
                fOnAppLoad: function() {
                    setIframeWidth();
                    setIframeHeight();
                },
                fOnBeforeUnload: function() {
                    setIframeWidth();
                    setIframeHeight();
                }
            });
        }
        function setIframeWidth() {
            var iframe = document.getElementById("input_content_iframe");
            if (iframe) {
                iframe.style.width = '100%';
            }
        }

        function setIframeHeight() {
            var iframe = document.getElementById("input_content_iframe");
            if (iframe) {
                var innerDoc = iframe.contentDocument || iframe.contentWindow.document;
                iframe.style.height = innerDoc.body.scrollHeight + "px";
            }
        }

        window.addEventListener("resize", function() {
            setIframeWidth();
            setIframeHeight();
        });

        function onInput(input, max_length){
            if(input.value.length > max_length){
                alert('최대 문자열 길이는 ' + max_length + 'byte 입니다.')
                return input.value = input.value.substring(0, max_length);
            }
        }

        function submitClick(){
            if(document.getElementById("input_title").value == ''){
                alert('제목을 작성해주세요.')
                return;
            }
            else if(posting_index < 0 && document.getElementById("input_category").value < 1){
                alert('분류를 선택해주세요.');
                return;
            }
            else if(document.getElementById("input_content").value == '<p>&nbsp;</p>'){
                alert('내용을 작성해주세요.')
                return;
            }
            
            oEditors.getById["input_content"].exec("UPDATE_CONTENTS_FIELD", []);

            var formData = new FormData();
            formData.append('user_id', getCookie('user_id'));
            formData.append('user_pw', getCookie('user_pw'));
            formData.append('posting_index', posting_index);

            var editorStr = document.getElementById("input_content").value;
            editorStr = editorStr.replaceAll('<div', '<p');
            editorStr = editorStr.replaceAll('</div>', '</p>');

            var editorFrame = document.getElementById('editor_frame');
            var inputFrame = editorFrame.contentWindow.document.getElementById('se2_iframe');
            var imgClass = inputFrame.contentWindow.document.getElementsByClassName('photo');

            var inputArea = inputFrame.contentWindow.document.getElementsByClassName('se2_inputarea')[0];
            var summaryStr = inputArea.innerText.substring(0, 256);
            if(inputArea.innerText.length > 256) summaryStr += '...';
            summaryStr  = summaryStr.replaceAll('\n', ' ');

            formData.append('category_index', document.getElementById("input_category").value);
            formData.append('posting_title', document.getElementById("input_title").value);
            if(imgClass.length > 0){
                var path = imgClass[0].src.split('/res/');
                formData.append('posting_thumbnail', '/res/' + path[1]);
            }
            else
                formData.append('posting_thumbnail', '');
            formData.append('posting_summary', summaryStr);
            formData.append('posting_content', editorStr);

            var xhr = new XMLHttpRequest();
            var url = 'post/write_posting';
            xhr.open('POST', url);
            xhr.onreadystatechange = function () {
                if (xhr.readyState != XMLHttpRequest.DONE) return;
                if (xhr.status != 200) {
                    alert('Server Error (' + xhr.status + ')');
                    return;
                }
                
                var result = JSON.parse(this.responseText);
                if(result['state'] == 0)
                    location.href = 'reader.php?posting_index=' + result['posting_index'];
                else 
                    alert('포스팅 실패 (' + result['etc'] + ')');
            }
            xhr.send(formData);
        }
    </script>
</body>
</html>
