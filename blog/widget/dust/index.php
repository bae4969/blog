<!doctype html>
<html lang=ko>

<head>
    <meta charset='utf-8'>
    <title>dust</title>
    <link type="text/css" rel="stylesheet" href="css/index.css">
</head>

<body>
    <div id=info0>
        <b>대기오염</b>
    </div>
    <select id='selectType' onchange='selectType()'>
        <option value="SO2">아황산가스</option>
        <option value="CO">일산화탄소</option>
        <option value="O3">오존</option>
        <option value="NO2">이산화질소</option>
        <option value="PM10" selected>미세먼지</option>
        <option value="PM25">초미세먼지</option>
    </select>
    <div id=dust_contain>
        <div id=dust_img_contain><img id=now_img src='res/map.png'></div>
        <div id=busan class=loc></div>
        <div id=chungbuk class=loc></div>
        <div id=chungnam class=loc></div>
        <div id=daegu class=loc></div>
        <div id=daejeon class=loc></div>
        <div id=gangwon class=loc></div>
        <div id=gwangju class=loc></div>
        <div id=gyeongbuk class=loc></div>
        <div id=gyeonggi class=loc></div>
        <div id=gyeongnam class=loc></div>
        <div id=incheon class=loc></div>
        <div id=jeju class=loc></div>
        <div id=jeonbuk class=loc></div>
        <div id=jeonnam class=loc></div>
        <div id=sejong class=loc></div>
        <div id=seoul class=loc></div>
        <div id=ulsan class=loc></div>
    </div>
    <div id=info1>
        자료제공 : 에어코리아
    </div>
    
    <script type="text/javascript">
        var dust;

        window.onload = function(){
            initData();
        }
        function initData(){
            var xhr = new XMLHttpRequest();
            var url = 'get/data';
            xhr.open('GET', url);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == XMLHttpRequest.DONE && xhr.status == 200){
                    var data = JSON.parse(this.responseText);
                    if(data['state'] == 0){
                        dust = data['data'];
                        setData('PM10');
                    }
                }
            };
            xhr.send();
        }

        function selectType(){
            var selectType = document.getElementById('selectType');
            setData(selectType.options[selectType.selectedIndex].value);
        }
        
        function setData(type){
            for(var i = 0; i < dust.length; i++){
                var location = dust[i]['name'];
                var value = dust[i][type];
                var loc_div = document.getElementById(location);
                loc_div.innerHTML = '&nbsp' + value + '&nbsp';
                switch(type){
                    case 'SO2':
                        if(value < 0.02)        loc_div.style.background = '#005EAE';
                        else if(value < 0.05)   loc_div.style.background = '#4B9D2B';
                        else if(value < 0.15)   loc_div.style.background = '#C37D02';
                        else                    loc_div.style.background = '#9C1615';
                        break;
                    case 'CO':
                        if(value < 2.0)         loc_div.style.background = '#005EAE';
                        else if(value < 9.0)    loc_div.style.background = '#4B9D2B';
                        else if(value < 15.0)   loc_div.style.background = '#C37D02';
                        else                    loc_div.style.background = '#9C1615';
                        break;
                    case 'O3':
                        if(value < 0.03)        loc_div.style.background = '#005EAE';
                        else if(value < 0.09)   loc_div.style.background = '#4B9D2B';
                        else if(value < 0.15)   loc_div.style.background = '#C37D02';
                        else                    loc_div.style.background = '#9C1615';
                        break;
                    case 'NO2':
                        if(value < 0.03)        loc_div.style.background = '#005EAE';
                        else if(value < 0.06)   loc_div.style.background = '#4B9D2B';
                        else if(value < 0.20)   loc_div.style.background = '#C37D02';
                        else                    loc_div.style.background = '#9C1615';
                        break;
                    case 'PM10':
                        if(value < 30)          loc_div.style.background = '#005EAE';
                        else if(value < 80)     loc_div.style.background = '#4B9D2B';
                        else if(value < 150)    loc_div.style.background = '#C37D02';
                        else                    loc_div.style.background = '#9C1615';
                        break;
                    case 'PM25':
                        if(value < 15)          loc_div.style.background = '#005EAE';
                        else if(value < 35)     loc_div.style.background = '#4B9D2B';
                        else if(value < 75)     loc_div.style.background = '#C37D02';
                        else                    loc_div.style.background = '#9C1615';
                        break;
                }
            }
        }
    </script>
</body>

</html>
