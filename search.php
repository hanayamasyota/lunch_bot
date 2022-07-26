<?php
function get_restaurant_information($lat, $lon) {
    $latitude = round($lat, 6);
    $longitude = round($lon, 6);
    $range = 1;

    // クエリをまとめる
    $query = [
    'key' => '7264b03648f65bd1',
    'lat' => $latitude, // 緯度
    'lng' => $longitude, // 経度
    'range' => $range, // 検索範囲
    // 'start' => $start, // 検索の開始位置
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

#テキストフォーマット
function text_format($message) {
    return [
        "type" => "text",
        "text" => $message
    ];
}


function renderJson($json) {
    $restaurant_length = $json->{"results"}->{"results_available"};
    if ($restaurant_length < 1) {
        $txt = "周辺にお店が見つかりませんでした。";
        return $txt;
    }
    $temp = $json->{"results"};
    $txt = "周辺300m以内に".$restaurant_length."件見つかりました。\r\nテストで5件まで表示します。\r\n";
    for ($i = 0; $i < $restaurant_length; $i++) {
        #店名、住所、ジャンル、URL(ホットペッパーのページ)
        $txt .= "店名：".$temp->{"shop"}[$i]->{"name"}."\r\n";
        $txt .= "ジャンル：".$temp->{"shop"}[$i]->{"genre"}->{"name"}."\r\n";
        // $txt .= "url:".$temp->{"shop"}[$i]->{"urls"}->{"pc"}."\r\n";
        $txt .= "店舗ID:".$temp->{"shop"}[$i]->{"id"}."\r\n";
        $txt .= "ジャンル:".$temp->{"shop"}[$i]->{"genre"}->{"name"}."\r\n";
        if ($i > 3) {
            $txt .= "Powered by http://webservice.recruit.co.jp/ホットペッパー Webサービス";
            break;
        }
        $txt .= "\r\n";
    }
    return $txt;
}