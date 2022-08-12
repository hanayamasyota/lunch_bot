<?php
function get_restaurant_information($lat, $lon) {
    $latitude = round($lat, 6);
    $longitude = round($lon, 6);
    $range = 2;

    // クエリをまとめる
    $query = [
    'key' => '7264b03648f65bd1',
    'lat' => $latitude, // 緯度
    'lng' => $longitude, // 経度
    'range' => $range, // 検索範囲
    'start' => 1,
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
            $result .= "\nPowered by http://webservice.recruit.co.jp/ホットペッパー Webサービス";
            break;
        }
        $result .= "\r\n";
    }
    $result_txt = "周辺500m以内に".$restaurant_length."件見つかりました。\r\n10件まで表示します。\r\n\n" . $result;
    return $result_txt;
}
?>