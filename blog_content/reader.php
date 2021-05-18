<!-- content/reader.php -->
<!doctype html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Blog</title>
    <link rel="stylesheet" href="/css/after.css">
    <link rel="stylesheet" href="/css/blog/main_outer.css">
    <link rel="stylesheet" href="/css/blog/main_header.css">
    <link rel="stylesheet" href="/css/blog/main_aside.css">
    <link rel="stylesheet" href="/css/blog/main_detailContent.css">
    <link rel="stylesheet" href="/css/blog/main_footer.css">

    <?php
        include '../php/blog.php';
        $user = checkUser($_POST['id'], $_POST['pw']);
    ?>

    <script src="/js/blog.js"> </script>
    <script>
        window.onload = function() {
            <?php echoMainOnload($user['user_index']) ?>
        }

        function editClick(){
            var form = getDefaultPostForm('/blog_content/edit');
            var hiddenField = document.createElement('input');
            hiddenField.setAttribute('type', 'hidden');
            hiddenField.setAttribute('name', 'content_index');
            hiddenField.setAttribute('value', <?php echo $_POST['content_index']; ?>);
            form.appendChild(hiddenField);
            document.body.appendChild(form);
            form.submit();
        }
        
        function deleteClick(){
            var form = getDefaultPostForm('/blog_content/deleteCheck');
            var hiddenField = document.createElement('input');
            hiddenField.setAttribute('type', 'hidden');
            hiddenField.setAttribute('name', 'content_index');
            hiddenField.setAttribute('value', <?php echo $_POST['content_index']; ?>);
            form.appendChild(hiddenField);
            document.body.appendChild(form);
            form.submit();
        }
        
        function restoreClick(){
            var form = getDefaultPostForm('/blog_content/restoreCheck');
            var hiddenField = document.createElement('input');
            hiddenField.setAttribute('type', 'hidden');
            hiddenField.setAttribute('name', 'content_index');
            hiddenField.setAttribute('value', <?php echo $_POST['content_index']; ?>);
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
            <aside>
                <div id=profile> profile </div>
                <ul id=category> <?php echoAsideList($user['level']); ?> </ul>
            </aside>
            <?php echoDetailContent($user['user_index'], $user['level'], $_POST['content_index']); ?>
        </section>
        <footer>
            <p>Contact : bae4969@naver.com</br>
            Github : <a class=footer href=https://github.com/bae4969>https://github.com/bae4969</a></p>
        </footer>
    </div>
</body>
</html>