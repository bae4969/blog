<!--login.php -->
<!doctype html>
<html lang=ko>
<head>
    <meta charset='utf-8'>
    <title>Login</title>
    <link type="text/css" rel="stylesheet" href="css/login.css">
</head>
<body>
    <div id='main'>
        <header>
            <div id=topLeft OnClick='location.href="index.php"'>
                Home
            </div>
        </header>
        <div id='inputLayout'>
            <div class='title'>
                <img id=mainTitle OnClick='location.href="index.php"' src="res/title.png" alt="Index Page" height="100%" />
            </div>

            <div class='input'>
                <input id='text_id' class='input' type='text' placeholder='ID'
                    onkeyup="if(window.event.keyCode==13){loginClick()}" />
            </div>

            <div class='input'>
                <input id='text_pw' class='input' type='password' placeholder='PW'
                    onkeyup="if(window.event.keyCode==13){loginClick()}" />
            </div>

            <div class='input'>
                <button id='btn_login' onclick='loginClick()'>Login</button>
            </div>
        </div>
    </div>
    
    <script type="text/javascript" src="/js/sha256.js"></script>
    <script type="text/javascript" src="/js/basicFunc.js"></script>
    <script type="text/javascript">
        var user;

        function loginClick() {
            var user_id = document.getElementById("text_id").value;
            var user_pw = sha256(document.getElementById("text_pw").value);

            var xhr = new XMLHttpRequest();
            var url = 'get/login_verify';
            url += '?user_id=' + user_id;
            url += '&user_pw=' + user_pw;
            xhr.open('GET', url);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == XMLHttpRequest.DONE) {
                    if(xhr.status == 200){
                        user = JSON.parse(this.responseText);
                        if(user['state'] == 0){
                            setCookie("user_id", user_id, 1);
                            setCookie("user_pw", user_pw, 1);
                            location.href = '/index.php';
                        }
                        else {
                            alert(user['etc']);
                        }
                    }
                    else{
                        alert('Server Error (' + xhr.status + ')');
                    }
                }
            };
            xhr.send();
        }
    </script>
</body>
</html>
