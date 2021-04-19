<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Login Checking</title>
    <style>
/************************************outer************************************/

    a:link {
        color: #C3C3C3;
        text-decoration: none;
    }

    a:visited {
        color: #C3C3C3;
        text-decoration: none;
    }

    a:hover {
        color: #C3C3C3;
        text-decoration: none;
    }

    html{
        height: 97%;
    }

    body {
        height: 100%;
        background: #181A1B;
        margin: 0;
        padding: 0;
        color: white;
        list-style: none;
        font-family: 'arial';
    }

/************************************main************************************/

    div{
        height: 100%;
        height: 20%;
        position: absolute; left: 50%; top: 50%; 
        transform: translate(-50%, -50%); text-align: center;
    }

    p#text{
        font-size: 5ex;
        font-weight: bold;
    }

    </style>
</head>

<script>
    window.onload = function() {
        <?php
            include "sql/basic.php";
            parse_str(getenv("QUERY_STRING"), $array);
            $user = userCheck($array["id"], $array["pw"]);
        ?>
        if(<?php echo $user["valid"]; ?> == 1){
            localStorage.setItem("isLogin", true);
            localStorage.setItem("id", <?php echo '"'.$array["id"].'"'; ?>);
            localStorage.setItem("pw", <?php echo '"'.$array["pw"].'"'; ?>);
		    location.href = "index?id=" + <?php echo '"'.$array["id"].'"'; ?> + "&pw=" + <?php echo '"'.$array["pw"].'"'; ?>;
        }
        else if(<?php echo $user["valid"]; ?> == -1){
            alert("Banned User");
            localStorage.setItem("isLogin", false);
		    location.href = "login";
        }
        else{
            alert("Worng ID or PW, check your input");
            localStorage.setItem("isLogin", false);
		    location.href = "login";
        }
    }
</script>

<body>
    <div>
        <p id='text'>Login Checking ...</p>
    </div>
</body>
</html>