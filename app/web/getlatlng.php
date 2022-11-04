<?php
session_start();
?>

<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>ひるまちGO|位置情報登録フォーム</title>
 
    <style type="text/css">
        body {
            background-color: #f0e68c;
        }

        #map {
            border: 0.3rem solid #f5f5f5;
            margin: 1em 1em 1em;
            height: 700px;
            width: auto;
        }

        p {
            margin-left: 5%;
        }

    </style>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC2tnzNvq7H-AGrGdPrUdSpRTIASeim0nk"></script>
    <script>
        var marker = null;
        var lat = 36.06351665;  // ここの座標は
        var lng = 136.22271022; // 現在位置の座標を持ってきてね
        var map;

        function init() {  //ページロード時に初期化
            map = new google.maps.Map(document.getElementById('map'), {
            zoom: 18, center: {lat: lat, lng: lng}
        });
 
            document.getElementById('lat').value = lat;
            document.getElementById('lng').value = lng;
 
            //初期ピン
            marker = new google.maps.Marker({
                map: map, position: new google.maps.LatLng(lat, lng),
            });
    
            //クリックしたとこにピン立つ
            map.addListener('click', function(e) {
                clickMap(e.latLng, map);
            });
        }
 

        function clickMap(geo, map) { // マップクリック時
            lat = geo.lat();
            lng = geo.lng();
    
            //小数点以下6桁に丸める場合
            //lat = Math.floor(lat * 1000000) / 1000000);
            //lng = Math.floor(lng * 1000000) / 1000000);
    
            document.getElementById('lat').value = lat;
            document.getElementById('lng').value = lng;
    
            //中心に動く
            map.panTo(geo);
    
            marker.setMap(null);
            marker = null;
            marker = new google.maps.Marker({
                map: map, position: geo 
            });
        }

        function search(){ // 検索ボタンクリック時
            var place = document.getElementById('place').value;
            var geocoder = new google.maps.Geocoder();
            
            geocoder.geocode({"address" : place}, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    var location = results[0].geometry.location;
                    var lat = location.lat();
                    var lng = location.lng();
                    var latlng = {lat: lat, lng: lng};

                    map.setCenter(latlng);
               }
            });
        }
    </script>
 

    <body onload="javascript:init();">
        <div id="map" style="margin-top: 10px; margin-bottom:15px;"></div><br>
        <p>検索欄に住所や地名を入力すると、その付近に移動できます。<br>地図でクリックした位置の座標が表示されます。<br>場所を地図でクリックして確定してください。</p>
        <center>
            <input type="text" value="" size="20" id="place" placeholder="住所や地名を入力">
            <button type="button" onclick="search()">検索</button><br><br>
        <?php if ($_GET["type"] == 'shop') {
            $url = 'owner/post_shop_owner.php';
        } else if ($_GET["type"] == 'event') {
            $url = 'owner/post_event_owner.php';
        } else {
            $url = 'post_shop_event.php';
        } ?>
        <form method="post" action="<?php echo $url; ?>">
            <div class="input">
                緯度：<input type="text" id="lat" name="lat" value="" size="20">　
                経度：<input type="text" id="lng" name="lng" value="" size="20"><br><br>
            </div>
        <input type="submit" value="確定する" />
        </form>
        </center>
    </body>

</html>