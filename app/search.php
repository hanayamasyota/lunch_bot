<?php
function getRestaurantInfomation($lat, $lon, $range=2) {
    $latitude = round($lat, 6);
    $longitude = round($lon, 6);

    // クエリをまとめる
    $query = [
        'key' => '7264b03648f65bd1',
        'lat' => $latitude, // 緯度
        'lng' => $longitude, // 経度
        'range' => $range, // 検索範囲
        'lunch' => 1,
        'count' => 100,
        'format' => 'json',
    ];
    // グルメサーチAPIからjsonを取得
    $message = "";
    $url = 'http://webservice.recruit.co.jp/hotpepper/gourmet/v1/?';
    $url .= http_build_query($query);
    $response = file_get_contents($url);

    $json = json_decode($response);
    $data_array = renderJson($json);

    return $data_array;
}

function renderJson($json) {
    $resultLength = $json->{"results"}->{"results_available"};
    if ($resultLength < 1) {
        $result = false;
        return $result;
    }
    $temp = $json->{"results"};

    $data_array = array();
    for ($i = 0; $i < $resultLength; $i++) {
        $array = array($i => array(
            "name" => $temp->{'shop'}[$i]->{'name'},
            "id" => $temp->{'shop'}[$i]->{'id'},
            "genre" => $temp->{'shop'}[$i]->{'genre'}->{'name'},
            "url" => $temp->{'shop'}[$i]->{'urls'}->{'pc'},
            "image" => bytea_import($temp->{'shop'}[$i]->{'photo'}->{'mobile'}->{'s'}),
            "number" => $i,
            "latitude" => $temp->{'shop'}[$i]->{'lat'},
            "longitude" => $temp->{'shop'}[$i]->{'lng'},
            "shoplength" => $resultLength,
        )); 
        $data_array += $array;
    }
    return $data_array;
}