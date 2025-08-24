<!-- blog.php -->
<!doctype html>
<html lang=ko>

<head>
    <meta charset='utf-8'>
    <title>Developer Blog</title>
    <link type="text/css" rel="stylesheet" href="css/index.css">
</head>

<body>
    <div id=main>
        <header>
            <div id=topLeft OnClick='location.href="/index.php"'>Home</div>
            <div id=topRight onclick=loginoutClick()></div>
            <div id=topWrite onclick=writePostingClick()></div>
            <div id=title>
                <img id=mainTitle OnClick='location.href="index.php"' src="res/title.png" alt="Blog Page" />
            </div>
        </header>
        <section>
            <aside>
                <div id=profile>profile</div>
                <div id=user_count>user count</div>
                <ul id=category></ul>
                <div id=search_posting_div>
                    <select id='search_category_list'>
                        <option value=-1>분류 선택</option>
                    </select>
                    <button id='search_posting_btn' onclick='searchPostingClick()'>검색</button>
                    <input id='search_posting_text' type='text' placeholder='제목' onkeyup="if(window.event.keyCode==13){searchPostingClick()}" />
                </div>
            </aside>
            <div id=postings>
                <div>
                    <div id=left></div>
                    <div id=right></div>
                </div>
                <div id=pages>
                </div>
                <div id=temp>
                </div>
            </div>
        </section>
        <footer>
            <p>Contact : bae4969@naver.com</br>
                Github : <a class=footer href=https://github.com/bae4969>https://github.com/bae4969</a></p>
        </footer>
    </div>

    <script type="text/javascript" src="/js/basicFunc.js"></script>
    <script type="text/javascript">
        var user_info_row;
        var showState = 0;
        var category_index = -1;
        var search_string = "";
        var page_index = 0;
        var page_size = 10;
        var pageCount = 0;
        var loadCount = 0;

        window.onload = function() {
            var params = {};
            location.search.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(str, key, value) {
                params[key] = value;
            });
            if (params['category_index'])
                category_index = params['category_index'];
            if (params['search_string'])
                search_string = decodeURI(decodeURIComponent(params['search_string']));
            if (params['page_index'])
                page_index = params['page_index'];

            verifyLogin();
            initProfile();
            initCategoryList();
            initPostingList();
        }
        window.onresize = function() {
            setPostingList();
        }

        function loginoutClick() {
            if (user_info_row['state'] == 0) {
                deleteCookie('user_id');
                deleteCookie('user_pw');
                alert("로그아웃");
                location.href = 'index.php';
            } else {
                location.href = '/login.php';
            }
        }

        function writePostingClick() {
            location.href = "/writer.php?category_index=" + category_index;
        }

        function verifyLogin() {
            var xhr = new XMLHttpRequest();
            var url = '/get/login_verify';
            url += '?user_id=' + getCookie('user_id');
            url += '&user_pw=' + getCookie('user_pw');
            xhr.open('GET', url);
            xhr.onreadystatechange = function() {
                if (xhr.readyState != XMLHttpRequest.DONE) return;
                if (xhr.status != 200) {
                    alert('Server Error (' + xhr.status + ')');
                    return;
                }

                user_info_row = JSON.parse(this.responseText);
                if (user_info_row['state'] == 0) {
                    document.getElementById("topRight").innerHTML = "로그아웃";
                    document.getElementById("topWrite").innerHTML = "글쓰기";
                } else {
                    document.getElementById("topRight").innerHTML = "로그인";
                    if (document.getElementById("topWrite") !== null)
                        document.getElementById("topWrite").innerHTML = "";
                }
            };
            xhr.send();
        }

        function initProfile() {
            var xhr = new XMLHttpRequest();
            var url = 'get/profile_info';
            xhr.open('GET', url);
            xhr.onreadystatechange = function() {
                if (xhr.readyState != XMLHttpRequest.DONE) return;
                if (xhr.status != 200) {
                    alert('Server Error (' + xhr.status + ')');
                    return;
                }

                var profile_info = JSON.parse(this.responseText);
                if (profile_info['state'] != 0) {
                    alert('프로파일 초기화 오류 (' + profile_info['state'] + ')');
                    return;
                }

                var user_count = document.getElementById('user_count');
                user_count.innerHTML = '이번주 방문자 수 : ' + profile_info['weekly_visitors'];
            };
            xhr.send();
        }

        function initCategoryList() {
            var xhr = new XMLHttpRequest();
            var url = 'get/category_read_list';
            url += '?user_id=' + getCookie('user_id');
            url += '&user_pw=' + getCookie('user_pw');
            xhr.open('GET', url);
            xhr.onreadystatechange = function() {
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


                var aside_ul = document.getElementById('category');
                var search_category_list = document.getElementById('search_category_list');
                for (var i = 0; i < category_list['data'].length; i++) {
                    var category_li = document.createElement('li');
                    aside_ul.appendChild(category_li);
                    category_li.className = 'category';
                    category_li.value = category_list['data'][i]['category_index']
                    category_li.innerHTML = category_list['data'][i]['category_name'];
                    category_li.onclick = function() {
                        location.href = 'index.php?category_index=' + this.value;
                    }

                    var option = document.createElement('option');
                    search_category_list.appendChild(option);
                    option.value = category_list['data'][i]['category_index']
                    option.innerHTML = category_list['data'][i]['category_name'];
                    if (category_index >= 0 && option.value == category_index)
                        option.selected = true;
                }
                document.getElementById('search_posting_text').value = search_string;
            };
            xhr.send();
        }

        function initPostingList() {
            loadCount = 0;
            var xhr = new XMLHttpRequest();
            var url = 'get/summary_posting_list';
            url += '?user_id=' + getCookie('user_id');
            url += '&user_pw=' + getCookie('user_pw');
            url += '&category_index=' + category_index;
            url += '&search_string=' + search_string;
            url += '&page_index=' + page_index;
            url += '&page_size=' + page_size;
            xhr.open('GET', url);
            xhr.onreadystatechange = function() {
                if (xhr.readyState != XMLHttpRequest.DONE) return;
                if (xhr.status != 200) {
                    alert('Server Error (' + xhr.status + ')');
                    return;
                }

                var posting_list = JSON.parse(this.response);
                if (posting_list['state'] != 0) {
                    alert('포스팅 초기화 오류 (' + category_list['state'] + ')');
                    return;
                }


                var temp_container = document.getElementById('temp');
                var left_container = document.getElementById('left');
                var right_container = document.getElementById('right');
                var page_container = document.getElementById('pages');
                while (temp_container.hasChildNodes())
                    temp_container.removeChild(temp_container.firstChild);
                while (left_container.hasChildNodes())
                    left_container.removeChild(left_container.firstChild);
                while (right_container.hasChildNodes())
                    right_container.removeChild(right_container.firstChild);
                while (page_container.hasChildNodes())
                    page_container.removeChild(page_container.firstChild);

                var total_count = posting_list['total_count'];
                pageCount = (total_count - (total_count % page_size)) / page_size;
                if (total_count % page_size != 0)
                    pageCount += 1;

                for (var i = 0; i < posting_list['data'].length; i++) {
                    var container = document.createElement('div');
                    var title = document.createElement('div');
                    var date = document.createElement('div');
                    var writer = document.createElement('div');
                    var hr = document.createElement('hr');
                    var thumbnail_container = document.createElement('div');
                    var thumbnail = document.createElement('img');
                    var summary = document.createElement('div');

                    container.appendChild(title);
                    container.appendChild(date);
                    container.appendChild(writer);
                    container.appendChild(hr);
                    thumbnail_container.appendChild(thumbnail);
                    container.appendChild(thumbnail_container);
                    container.appendChild(summary);
                    temp_container.appendChild(container);

                    container.id = 'posting' + i;
                    container.value = posting_list['data'][i]['posting_index'];
                    if (posting_list['data'][i]['posting_state'] > 0)
                        container.className = 'posting_ban';
                    else
                        container.className = 'posting';
                    container.onclick = function() {
                        location.href = 'reader.php?posting_index=' + this.value;
                    }
                    title.className = 'posting_title';
                    title.innerHTML = posting_list['data'][i]['posting_title'];
                    date.className = 'posting_date';
                    date.innerHTML = posting_list['data'][i]['posting_first_post_datetime'];
                    writer.className = 'posting_writer';
                    writer.innerHTML = 'UID : ' + posting_list['data'][i]['user_index'];
                    thumbnail_container.className = 'posting_thumbnail_container';
                    thumbnail.className = 'posting_thumbnail';
                    thumbnail.src = posting_list['data'][i]['posting_thumbnail'];
                    summary.className = 'posting_summary';
                    summary.innerHTML = posting_list['data'][i]['posting_summary'];

                    if (posting_list['data'][i]['posting_thumbnail'] == '') {
                        loadCount += 1;
                        checkLoadPosting(posting_list['data'].length);
                    } else
                        thumbnail.onload = function() {
                            loadCount += 1;
                            checkLoadPosting(posting_list['data'].length);
                        }
                }

                var start = (page_index - (page_index % 10)) / 10;
                for (var i = -4; i < -2; i++) {
                    var button = document.createElement('button');
                    page_container.appendChild(button);
                    button.className = 'page';
                    button.value = i;
                    if (i == -4)
                        button.innerHTML = '<<';
                    else
                        button.innerHTML = '<';
                    button.onclick = function() {
                        pageClick(this)
                    }
                }
                for (var i = start; i < pageCount && i < 10; i++) {
                    var button = document.createElement('button');
                    page_container.appendChild(button);
                    if (i == page_index)
                        button.id = 'selectedPage';
                    button.className = 'page';
                    button.value = i;
                    button.innerHTML = i + 1;
                    button.onclick = function() {
                        pageClick(this)
                    }
                }
                for (var i = -2; i < 0; i++) {
                    var button = document.createElement('button');
                    page_container.appendChild(button);
                    button.className = 'page';
                    button.value = i;
                    if (i == -2)
                        button.innerHTML = '>';
                    else
                        button.innerHTML = '>>';
                    button.onclick = function() {
                        pageClick(this)
                    }
                }
            };
            xhr.send();
        }

        function checkLoadPosting(length) {
            if (loadCount >= length) {
                showState = 0;
                setPostingList();
            }
        }

        function setPostingList() {
            var postingSize = document.getElementsByClassName('posting').length +
                document.getElementsByClassName('posting_ban').length;
            var div_temp = document.getElementById('temp');
            var div_left = document.getElementById('left');
            var div_right = document.getElementById('right');

            if (showState != 1 && document.body.offsetWidth < 1600) {
                showState = 1;
                div_temp.style.height = 0;
                for (i = 0; i < postingSize; i++)
                    div_temp.appendChild(document.getElementById("posting" + i));

                div_left.style.width = '100%';
                div_right.style.width = '0%';
                for (i = 0; i < postingSize; i++)
                    div_left.appendChild(document.getElementById("posting" + i));
            } else if (showState != 2 && document.body.offsetWidth >= 1600) {
                showState = 2;
                div_temp.style.height = 0;
                for (i = 0; i < postingSize; i++)
                    div_temp.appendChild(document.getElementById("posting" + i));

                div_left.style.width = '50%';
                div_right.style.width = '50%';
                for (i = 0; i < postingSize; i++) {
                    if (div_left.offsetHeight > div_right.offsetHeight)
                        div_right.appendChild(document.getElementById("posting" + i));
                    else
                        div_left.appendChild(document.getElementById("posting" + i));
                }
            }
        }

        function selectSearchCategroyList(t_category_index) {
            var search_category_list = document.getElementById('search_category_list');
            for (var i = 0; i < category_list['data'].length; i++)
                if (t_category_index >= 0 && option.value == t_category_index)
                    option.selected = true;
        }

        function searchPostingClick() {
            var t_search_str = document.getElementById('search_posting_text').value;
            if (t_search_str.length < 2)
                alert("검색 문자는 최소 2자 이상이어야 합니다.")
            else
                location.href =
                "/index.php" +
                "?category_index=" + document.getElementById("search_category_list").value +
                "&search_string=" + encodeURI(encodeURIComponent(t_search_str));
        }

        function pageClick(ele) {
            temp_page = page_index;
            if (ele.value < 0) {
                if (ele.value == -4)
                    page_index -= 10;
                else if (ele.value == -3)
                    page_index -= 1;
                else if (ele.value == -2)
                    page_index += 1;
                else if (ele.value == -1)
                    page_index += 10;

                if (page_index < 0)
                    page_index = 0;
                if (page_index >= pageCount)
                    page_index = pageCount - 1;
            } else
                page_index = ele.value;

            if (temp_page != page_index)
                initPostingList();
        }
    </script>
</body>

</html>