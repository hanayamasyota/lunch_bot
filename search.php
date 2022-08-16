<?php
function get_restaurant_information($lat, $lon, $start) {
    $latitude = round($lat, 6);
    $longitude = round($lon, 6);
    $range = 2;

    // クエリをまとめる
    $query = [
    'key' => '7264b03648f65bd1',
    'lat' => $latitude, // 緯度
    'lng' => $longitude, // 経度
    'range' => $range, // 検索範囲
    'lunch' => 1,
    'start' => $start,
    'count' => 5,
    'format' => 'json',
    ];
    // グルメサーチAPIからjsonを取得
    $message = "";
    $url = 'http://webservice.recruit.co.jp/hotpepper/gourmet/v1/?';
    $url .= http_build_query($query);
    $response = file_get_contents($url);

    $json = json_decode($response);
    $message .= renderJson($json);

    return $message;
}

function renderJson($json) {
    $restaurant_length = $json->{"results"}->{"results_available"};
    if ($restaurant_length < 1) {
        $result = "周辺にお店が見つかりませんでした。";
        return $result;
    }
    $temp = $json->{"results"};
    $result = "";
    for ($i = 0; $i < $restaurant_length; $i++) {
        #店名、店ID、ジャンル、予算(ホットペッパーのページ)
        $result .= "店名:  ".$temp->{"shop"}[$i]->{"name"}."\r\n";
        $result .= "店舗ID: ".$temp->{"shop"}[$i]->{"id"}."\r\n";
        $result .= "ジャンル: ".$temp->{"shop"}[$i]->{"genre"}->{"name"}."\r\n";
        $result .= "予算: ".$temp->{"shop"}[$i]->{"budget"}->{"average"}."\r\n";
        if ($i > 8) {
            break;
        } else {
            $result .= ';';
        }
        $result .= "\r\n";
    }
    $result_txt = "周辺500m以内に".$restaurant_length."件見つかりました。\r\n10件まで表示します。\r\n\n" . $result;
    return $result_txt;
}
?>

<?php
function get_restaurant_information2($lat, $lon, $page) {
    $latitude = round($lat, 6);
    $longitude = round($lon, 6);
    //範囲も変えられるようにする？
    $range = 2;
    $start = $page * 5;

    // クエリをまとめる
    $query = [
        'key' => '7264b03648f65bd1',
        'lat' => $latitude, // 緯度
        'lng' => $longitude, // 経度
        'range' => $range, // 検索範囲
        'lunch' => 1,
        'start' => ($start+1),
        'count' => 5,
        'format' => 'json',
    ];
    // グルメサーチAPIからjsonを取得
    $message = "";
    $url = 'http://webservice.recruit.co.jp/hotpepper/gourmet/v1/?';
    $url .= http_build_query($query);
    $response = file_get_contents($url);

    $json = json_decode($response);
    $data_array = renderJson2($json, $start);

    return $data_array;
}

function renderJson2($json, $start) {
    $restaurant_length = $json->{"results"}->{"results_available"};
    if ($restaurant_length < 1) {
        $result = "周辺にお店が見つかりませんでした。";
        return $result;
    }
    $temp = $json->{"results"};
    $resultTxt = "周辺500m以内に".$restaurant_length."件見つかりました。\r\n5件まで表示します。\r\n\n" . $result;

    $data_array = array();
    for ($i = 0; $i < 5; $i++) {
        if ($restaurant_length-$start <= $i) {
            break;
        }
        $array = array($i => array(
            "name" => $temp->{'shop'}[$i]->{'name'},
            "id" => $temp->{'shop'}[$i]->{'id'},
            "url" => $temp->{'shop'}[$i]->{'urls'}->{'pc'},
            "image" => $temp->{'shop'}[$i]->{'photo'}->{'mobile'}->{'s'},
        )); 
        $data_array += $array;
    }
    return $data_array;
}
?>