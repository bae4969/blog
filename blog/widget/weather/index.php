<!-- weather.php -->
<!doctype html>
<html lang=ko>

<head>
    <meta charset='utf-8'>
    <title>Weather</title>
    <link type="text/css" rel="stylesheet" href="css/index.css">
</head>

<body>
    <div id=info0>
    </div>
    <select id='geo_name' onchange='selectGeo()'>
    </select>
    <div id=now>
        <div id=now_img_contain><img id=now_img src=''></div>
        <div id=now_detail></div>
    </div>
    <hr id=centor_line>
    <div id=info1>
        내일(오전,오후)의 날씨
    </div>
    <div id=tomm>
        <div id=tomm0>
            <div id=tomm0_img_contain><img id=tomm0_img src=''></div>
            <div id=tomm0_detail></div>
        </div>
        <div id=tomm1>
            <div id=tomm1_img_contain><img id=tomm1_img src=''></div>
            <div id=tomm1_detail></div>
        </div>
    </div>
    <div id=info2>
        자료제공 : 기상청
    </div>
    
    <script type="text/javascript">
        var geo;
        var now;
        var after;

        var geo_name = '서울특별시';
        var geo_sub = '용산구';

        window.onload = function(){
            var today = new Date();
            if(today.getHours() < 2 || (today.getHours() == 2 && today.getMinutes() < 11))
                today.setDate(today.getDate() - 1);
            document.getElementById('info0').innerHTML = '<b> ' + (today.getMonth() + 1) + '월 ' + today.getDate() + '일 날씨</b>';
            initGeo();
        }
        function initGeo(){
            var xhr = new XMLHttpRequest();
            var url = 'get/geo';
            xhr.open('GET', url);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == XMLHttpRequest.DONE && xhr.status == 200){
                    var class_list = JSON.parse(this.responseText);
                    if(class_list['state'] == 0){
                        geo = class_list['data'];
                        geo_name = localStorage.getItem('weather_geo_name') == null ? '서울특별시' : localStorage.getItem('weather_geo_name');
                        geo_sub = localStorage.getItem('weather_geo_sub') == null ? '용산구' : localStorage.getItem('weather_geo_sub');
                        var x = localStorage.getItem('weather_x') == null ? 60 : localStorage.getItem('weather_x');
                        var y = localStorage.getItem('weather_y') == null ? 126 : localStorage.getItem('weather_y');
                        initSelect();
                        initWeather(x, y);
                    }
                }
            };
            xhr.send();
        }
        function initWeather(x, y){
            var xhr0 = new XMLHttpRequest();
            var url = 'get/now';
            url += '?x=' + x;
            url += '&y=' + y;
            xhr0.open('GET', url);
            xhr0.onreadystatechange = function () {
                if (xhr0.readyState == XMLHttpRequest.DONE && xhr0.status == 200){
                    var now_data = JSON.parse(this.responseText);
                    if(now_data['state'] == 0){
                        var xhr1 = new XMLHttpRequest();
                        var url = 'get/after';
                        url += '?x=' + x;
                        url += '&y=' + y;
                        xhr1.open('GET', url);
                        xhr1.onreadystatechange = function () {
                            if (xhr1.readyState == XMLHttpRequest.DONE && xhr1.status == 200){
                                var after_data = JSON.parse(this.responseText);
                                if(after_data['state'] == 0){
                                    now = now_data['data'];
                                    after = after_data['data'];
                                    setWeather();
                                }
                            }
                        };
                        xhr1.send();
                    }
                }
            };
            xhr0.send();
        }

        function initSelect(){
            var geo_name_selector = document.getElementById('geo_name');
            for(var i = 0; i < geo.length; i++){
                var option = document.createElement('option');
                option.innerText = geo[i]['main'] + ' | ' + geo[i]['sub'];
                option.value = geo[i]['main'] + ' | ' + geo[i]['sub'];
                geo_name_selector.append(option);
            }

            selectSort(geo_name_selector);

            var now_selected = geo_name + ' | ' + geo_sub;
            for(var i = 0; i < geo_name_selector.length; i++){
                if(geo_name_selector.options[i].value == now_selected){
                    geo_name_selector.options[i].selected = true;
                    break;
                }
            }
        }
        function selectSort(boxIdObj, isValuesort){
            var obj, sArr, oArr, idx, op;

            if (typeof boxIdObj == 'string') obj = document.getElementById(boxIdObj);
            else obj = boxIdObj;

            if (obj.tagName.toLowerCase() != 'select') return false;
            if (typeof isValuesort == 'undefined') isValuesort = false;

            sArr = new Array(obj.options.length);
            oArr = new Array;

            for (idx = 0; idx < obj.options.length; idx++)
            {
                if (isValuesort) sArr[idx] = obj.options[idx].value;
                else sArr[idx] = obj.options[idx].text;

                oArr[sArr[idx]] = obj.options[idx];
            }
            sArr.sort();

            for (idx in sArr) obj.appendChild(oArr[sArr[idx]]);
        }
        
        function selectGeo(){
            var geo_name_selector = document.getElementById('geo_name');
            var selectedVal = geo_name_selector.options[geo_name_selector.selectedIndex].value;
            selectedVal = selectedVal.split(' | ');
            localStorage.setItem('weather_geo_name', selectedVal[0]);
            localStorage.setItem('weather_geo_sub', selectedVal[1]);

            for(var i = 0; i < geo.length; i++){
                if(geo[i]['main'] == selectedVal[0] && geo[i]['sub'] == selectedVal[1]){
                    localStorage.setItem('weather_x', geo[i]['x']);
                    localStorage.setItem('weather_y', geo[i]['y']);
                    initWeather(geo[i]['x'], geo[i]['y']);
                    break;
                }
            }
        }

        function setWeather(){
            var weather_now = now[0];
            var weather_after0 = after[0];
            var weather_after1 = after[1];

            var img = document.getElementById('now_img');
            var detail = document.getElementById('now_detail');
            switch(weather_now['PTY']){
                case '0':
                    var today = new Date();
                    var hour = today.getHours();
                    var sky = hour < 12 ? weather_after0['SKY0'] * 1 : sky = weather_after0['SKY1'] * 1;
                        
                    if(sky <= 2){
                        if(hour < 6 || hour > 18)
                            img.src = 'res/SKY_Night.png';
                        else
                            img.src = 'res/SKY_Sunny.png';
                    }
                    else if(sky <= 4){
                        if(hour < 6 || hour > 18)
                            img.src = 'res/SKY_Night_Cloudy.png';
                        else
                            img.src = 'res/SKY_Cloudy.png';
                    }
                    else if(sky <= 10)  img.src = 'res/SKY_Overload.png';
                    break;
                case '1': img.src = 'res/PTY_Raindrop.png'; break;
                case '2': img.src = 'res/PTY_Raindrop_Weak_Snow.png'; break;
                case '3': img.src = 'res/PTY_Weak_Snow.png'; break;
                case '4': img.src = 'res/PTY_Shower.png'; break;
                case '5': img.src = 'res/PTY_Rain.png'; break;
                case '6': img.src = 'res/PTY_Sleet.png'; break;
                case '7': img.src = 'res/PTY_Snow.png'; break;
            }
            detail.innerText
                = '온도 : ' + weather_now['T1H'] + '℃\n'
                + '습도 : ' + weather_now['REH'] + '%\n'
                + '풍량  : ' + weather_now['WSD'] + 'm/s\n'
                + '강수량 : ' + weather_now['RN1'] + 'mm';

            var img = document.getElementById('tomm0_img');
            var detail = document.getElementById('tomm0_detail');
            switch(weather_after1['PTY0']){
                case '0':
                    var sky = weather_after1['SKY0'];
                    if(sky <= 2)        img.src = 'res/SKY_Sunny.png';
                    else if(sky <= 4)   img.src = 'res/SKY_Cloudy.png';
                    else if(sky <= 10)  img.src = 'res/SKY_Overload.png';
                    break;
                case '1': img.src = 'res/PTY_Raindrop.png'; break;
                case '2': img.src = 'res/PTY_Raindrop_Weak_Snow.png'; break;
                case '3': img.src = 'res/PTY_Weak_Snow.png'; break;
                case '4': img.src = 'res/PTY_Shower.png'; break;
                case '5': img.src = 'res/PTY_Rain.png'; break;
                case '6': img.src = 'res/PTY_Sleet.png'; break;
                case '7': img.src = 'res/PTY_Snow.png'; break;
            }
            detail.innerText
                = '온도 : ' + weather_after1['TMN'] + '℃\n'
                + '습도 : ' + weather_after1['REH0'] + '%\n'
                + '풍량  : ' + weather_after1['WSD0'] + 'm/s\n'
                + '강수확률 : ' + weather_after1['POP1'] + '%';


            var img = document.getElementById('tomm1_img');
            var detail = document.getElementById('tomm1_detail');
            switch(weather_after1['PTY1']){
                case '0':
                    var sky = weather_after1['SKY1'];
                    if(sky <= 2)        img.src = 'res/SKY_Sunny.png';
                    else if(sky <= 4)   img.src = 'res/SKY_Cloudy.png';
                    else if(sky <= 10)  img.src = 'res/SKY_Overload.png';
                    break;
                case '1': img.src = 'res/PTY_Raindrop.png'; break;
                case '2': img.src = 'res/PTY_Raindrop_Weak_Snow.png'; break;
                case '3': img.src = 'res/PTY_Weak_Snow.png'; break;
                case '4': img.src = 'res/PTY_Shower.png'; break;
                case '5': img.src = 'res/PTY_Rain.png'; break;
                case '6': img.src = 'res/PTY_Sleet.png'; break;
                case '7': img.src = 'res/PTY_Snow.png'; break;
            }
            detail.innerText
                = '온도 : ' + weather_after1['TMX'] + '℃\n'
                + '습도 : ' + weather_after1['REH1'] + '%\n'
                + '풍량  : ' + weather_after1['WSD1'] + 'm/s\n'
                + '강수확률 : ' + weather_after1['POP1'] + '%';
        }
    </script>
</body>

</html>
