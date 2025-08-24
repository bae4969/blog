function setCookie(name, val, exp) {
    exp = typeof exp !== 'undefined' ? exp : 2;
    var date = new Date();
    date.setTime(date.getTime() + exp * 3600000);
    document.cookie = name + '=' + val + ';expires=' + date.toUTCString() + ';path=/';
}

function getCookie(name, exp) {
    exp = typeof exp !== 'undefined' ? exp : 2;
    var value = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
    if (value != null) setCookie(name, value[2], exp);
    return value ? value[2] : null;
}

function deleteCookie(name) {
    var value = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
    if (value != null) setCookie(name, '', -1);
}