<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>ひるまちGO|位置情報登録フォーム</title>
 
    <style type="text/css">
        body {
            background-color: #add8e6;
        }

        #map {
            border: 0.5rem solid #7fffd4;
            margin: 1em;
            height: 1700px;
            width: auto;
        }

        .input {
            width: 50%;
            margin-left: auto;
            margin-right: auto;
        }

    </style>
 
<!-- Google Maps API -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC2tnzNvq7H-AGrGdPrUdSpRTIASeim0nk &callback=initMap"></script>
    <script>
        var marker = null;
        var lat = 36.06351665;
        var lng = 136.22271022;
 
        function init() {
            //初期化
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 18, center: {lat: lat, lng: lng}
            });
 
            document.getElementById('lat').value = lat;
            document.getElementById('lng').value = lng;
 
            //初期マーカー
            marker = new google.maps.Marker({
                map: map, position: new google.maps.LatLng(lat, lng),
            });
    
            //クリックイベント
            map.addListener('click', function(e) {
                clickMap(e.latLng, map);
            });
        }
 
        function clickMap(geo, map) {
            lat = geo.lat();
            lng = geo.lng();
    
            //小数点以下6桁に丸める場合
            //lat = Math.floor(lat * 1000000) / 1000000);
            //lng = Math.floor(lng * 1000000) / 1000000);
    
            document.getElementById('lat').value = lat;
            document.getElementById('lng').value = lng;
    
            //中心にスクロール
            map.panTo(geo);
    
            //マーカーの更新
            marker.setMap(null);
            marker = null;
            marker = new google.maps.Marker({
                map: map, position: geo 
            });
        }


        


        var geocoder = GClientGeocoder();

        function showAddress(address) {
            if (geocoder) {
                geocoder.getLatLng(
                    address,
                    function(point) {
                        if (!point) {
                            alert(address + " not found");
                        } else {
                            map.setCenter(point, 13);
                            var marker = new GMarker(point);
                            map.addOverlay(marker);
                            marker.OpenInforWindowHtml(address + "<br>(lat=" + point.lat() + ", lng=" + point.lng() + ")");
                        }
                    }
                );
            }
        }

    </script>
 

    <body onload="javascript:init(); ">

    <form method="POST" action="" onsubmit="showAddress(this.address.value); return false">
            <p>
                <input type="text" size="40" name="address" />
                <input type="submit" value="移動する" />
            </p>
        
        
        


        <div id="map" style="margin-top: 10px; margin-bottom:15px;"></div><br>
        <div class="input">
            緯度：<input type="text" id="lat" name="lat" value="" size="20">　経度：<input type="text" id="lng" name="lng" value="" size="20"><br><br>
        </div>
        <center><input type="submit" value="確定する" /></center>


    </form>
    </body>

</html>