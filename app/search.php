<?php
function get_restaurant_information($lat, $lon, $page, $range=2) {
    $latitude = round($lat, 6);
    $longitude = round($lon, 6);
    $start = $page * PAGE_COUNT;

    // クエリをまとめる
    $query = [
        'key' => '7264b03648f65bd1',
        'lat' => $latitude, // 緯度
        'lng' => $longitude, // 経度
        'range' => $range, // 検索範囲
        'lunch' => 1,
        'start' => ($start+1),
        'count' => PAGE_COUNT,
        'format' => 'json',
    ];
    // グルメサーチAPIからjsonを取得
    $message = "";
    $url = 'http://webservice.recruit.co.jp/hotpepper/gourmet/v1/?';
    $url .= http_build_query($query);
    $response = file_get_contents($url);

    $json = json_decode($response);
    $data_array = renderJson($json, $start);

    return $data_array;
}

function renderJson($json, $start) {
    $resultLength = $json->{"results"}->{"results_available"};
    if ($resultLength < 1) {
        $result = "周辺にお店が見つかりませんでした。";
        return $result;
    }
    $temp = $json->{"results"};
    $resultTxt = "周辺500m以内に".$resultLength."件見つかりました。\r\n".PAGE_COUNT."件ごとに表示します。\r\n\n" . $result;

    $data_array = array();
    for ($i = 0; $i < PAGE_COUNT; $i++) {
        if ($resultLength-$start <= $i) {
            break;
        }
        $array = array($i => array(
            "name" => $temp->{'shop'}[$i]->{'name'},
            "id" => $temp->{'shop'}[$i]->{'id'},
            "genre" => $temp->{'shop'}[$i]->{'genre'}->{'name'},
            "url" => $temp->{'shop'}[$i]->{'urls'}->{'pc'},
            "image" => $temp->{'shop'}[$i]->{'photo'}->{'mobile'}->{'s'},
            "number" => $start+($i+1),
            "latitude" => $temp->{'shop'}[$i]->{'lat'},
            "longitude" => $temp->{'shop'}[$i]->{'lng'},
            "shoplength" => $resultLength,
        )); 
        $data_array += $array;
    }
    return $data_array;
}
?>