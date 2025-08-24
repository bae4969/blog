<!doctype html>
<html lang=ko>

<head>
    <meta charset='utf-8'>
    <title>bitcoin_news</title>
    <link type="text/css" rel="stylesheet" href="css/bitcoin_news.css">
</head>

<body>
    <div id=info0>
        <b>네이버 뉴스 분석</b>
    </div>
    <div id=info1>
        <b>검색어 : 비트코인</b>
    </div>
    <div id=info2>
        <div id=btn_rotate onclick="setRotate()">ON</div>
        <div id=btn_info>자동 순환</div>
    </div>
    <div id=table_contain>
        <table>
        <th>순위</th>  <th>메인 연관</th>  <th>횟수</th>  <th>서브 연관</th>  <th>횟수</th>
        <tr><td class=rank>1</td><td class=main onclick='setSubData(1)'></td><td class=main_count onclick='setSubData(1)'></td><td class=sub></td><td class=sub_count></td></tr>
        <tr><td class=rank>2</td><td class=main onclick='setSubData(2)'></td><td class=main_count onclick='setSubData(2)'></td><td class=sub></td><td class=sub_count></td></tr>
        <tr><td class=rank>3</td><td class=main onclick='setSubData(3)'></td><td class=main_count onclick='setSubData(3)'></td><td class=sub></td><td class=sub_count></td></tr>
        <tr><td class=rank>4</td><td class=main onclick='setSubData(4)'></td><td class=main_count onclick='setSubData(4)'></td><td class=sub></td><td class=sub_count></td></tr>
        <tr><td class=rank>5</td><td class=main onclick='setSubData(5)'></td><td class=main_count onclick='setSubData(5)'></td><td class=sub></td><td class=sub_count></td></tr>
        <tr><td class=rank>6</td><td class=main onclick='setSubData(6)'></td><td class=main_count onclick='setSubData(6)'></td><td class=sub></td><td class=sub_count></td></tr>
        <tr><td class=rank>7</td><td class=main onclick='setSubData(7)'></td><td class=main_count onclick='setSubData(7)'></td><td class=sub></td><td class=sub_count></td></tr>
        <tr><td class=rank>8</td><td class=main onclick='setSubData(8)'></td><td class=main_count onclick='setSubData(8)'></td><td class=sub></td><td class=sub_count></td></tr>
        <tr><td class=rank>9</td><td class=main onclick='setSubData(9)'></td><td class=main_count onclick='setSubData(9)'></td><td class=sub></td><td class=sub_count></td></tr>
        <tr><td class=rank>10</td><td class=main onclick='setSubData(10)'></td><td class=main_count onclick='setSubData(10)'></td><td class=sub></td><td class=sub_count></td></tr>
        <tr><td class=rank>11</td><td class=main onclick='setSubData(11)'></td><td class=main_count onclick='setSubData(11)'></td><td class=sub></td><td class=sub_count></td></tr>
        <tr><td class=rank>12</td><td class=main onclick='setSubData(12)'></td><td class=main_count onclick='setSubData(12)'></td><td class=sub></td><td class=sub_count></td></tr>
        <tr><td class=rank>13</td><td class=main onclick='setSubData(13)'></td><td class=main_count onclick='setSubData(13)'></td><td class=sub></td><td class=sub_count></td></tr>
        <tr><td class=rank>14</td><td class=main onclick='setSubData(14)'></td><td class=main_count onclick='setSubData(14)'></td><td class=sub></td><td class=sub_count></td></tr>
        <tr><td class=rank>15</td><td class=main onclick='setSubData(15)'></td><td class=main_count onclick='setSubData(15)'></td><td class=sub></td><td class=sub_count></td></tr>
        <tr><td class=rank>16</td><td class=main onclick='setSubData(16)'></td><td class=main_count onclick='setSubData(16)'></td><td class=sub></td><td class=sub_count></td></tr>
        <tr><td class=rank>17</td><td class=main onclick='setSubData(17)'></td><td class=main_count onclick='setSubData(17)'></td><td class=sub></td><td class=sub_count></td></tr>
        <tr><td class=rank>18</td><td class=main onclick='setSubData(18)'></td><td class=main_count onclick='setSubData(18)'></td><td class=sub></td><td class=sub_count></td></tr>
        <tr><td class=rank>19</td><td class=main onclick='setSubData(19)'></td><td class=main_count onclick='setSubData(19)'></td><td class=sub></td><td class=sub_count></td></tr>
        <tr><td class=rank>20</td><td class=main onclick='setSubData(20)'></td><td class=main_count onclick='setSubData(20)'></td><td class=sub></td><td class=sub_count></td></tr>
        </table>
    </div>
    
    <script type="text/javascript">
        var bitcoin;
        var sub_index;
        var isRotate = true;

        window.onload = function(){
            initData();
        }
        function initData(){
            var xhr = new XMLHttpRequest();
            var url = 'get/bitcoin_news_data';
            xhr.open('GET', url);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == XMLHttpRequest.DONE && xhr.status == 200){
                    var data = JSON.parse(this.responseText);
                    if(data['state'] == 0){
                        bitcoin = data['data'];
                        sub_index = 1
                        setMainData();
                        setSubData(sub_index);

                        rotate = setInterval(function() {
                            if(isRotate){
                                sub_index++;
                                if(sub_index > 20)
                                    sub_index = 1;
                                setSubData(sub_index);
                            }
                        }, 7000);
                    }
                }
            };
            xhr.send();
        }

        function setRotate(){
            btn = document.getElementById('btn_rotate');
            if(isRotate){
                isRotate = false;
                btn.innerHTML = 'OFF';
            }
            else{
                isRotate = true;
                btn.innerHTML = 'ON';
            }
        }

        function setMainData(){
            main = document.getElementsByClassName('main');
            main_count = document.getElementsByClassName('main_count');
            for(i = 0; i < main.length; i++){
                main[i].innerHTML = bitcoin[i]['name'];
                main_count[i].innerHTML = bitcoin[i]['count'];
            }
        }
        function setSubData(index){
            sub_index = index;

            rank = document.getElementsByClassName('rank');
            main = document.getElementsByClassName('main');
            main_count = document.getElementsByClassName('main_count');

            for(i = 0; i < main.length; i++){
                rank[i].style.background = '#222426';
                main[i].style.background = '#222426';
                main_count[i].style.background = '#222426';
            }

            rank[index - 1].style.background = '#464646';
            main[index - 1].style.background = '#464646';
            main_count[index - 1].style.background = '#464646';

            sub = document.getElementsByClassName('sub');
            sub_count = document.getElementsByClassName('sub_count');
            for(i = 0; i < main.length; i++){
                sub[i].innerHTML = bitcoin[index * 20 + i]['name'];
                sub_count[i].innerHTML = bitcoin[index * 20 + i]['count'];
            }
        }
    </script>
</body>

</html>
