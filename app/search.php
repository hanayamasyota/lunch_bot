<?php
//画像、urlは5件ページ遷移ごとに取り出す
function getShowData($lat, $lon, $page, $range=2, $shopLength) {
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
        'start' => $start,
        'count' => 5,
        'format' => 'json',
    ];
    // グルメサーチAPIからjsonを取得
    $url = 'http://webservice.recruit.co.jp/hotpepper/gourmet/v1/?';
    $url .= http_build_query($query);
    $response = file_get_contents($url);

    $json = json_decode($response);
    $data_array = renderJsonShowData($json, $shopLength-$start);

    return $data_array;
}

function renderJsonShowData($json, $shopLength) {
    if ($shopLength < 1) {
        return false;
    }
    $temp = $json->{"results"};
    $data_array = array();
    for ($i = 0; $i < PAGE_COUNT; $i++) {
        if ($resultLength-$start <= $i) {
            break;
        }
        $array = array($i => array(
            "url" => $temp->{'shop'}[$i]->{'urls'}->{'pc'},
            "image" => $temp->{'shop'}[$i]->{'photo'}->{'mobile'}->{'s'},
        )); 
        $data_array += $array;
    }
    return [$data_array];
}

//検索結果分取り出す
function getRestaurantData($lat, $lon) {
    $latitude = round($lat, 6);
    $longitude = round($lon, 6);

    // クエリをまとめる
    $query = [
        'key' => '7264b03648f65bd1',
        'lat' => $latitude, // 緯度
        'lng' => $longitude, // 経度
        'range' => $range, // 検索範囲
        'lunch' => 1,
        'start' => 1,
        'count' => 100,
        'format' => 'json',
    ];
    // グルメサーチAPIからjsonを取得
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
        return false;
    }
    $temp = $json->{"results"};

    $data_array = array();
    for ($i = 0; $i < $resultLength; $i++) {
        if ($resultLength <= $i) {
            break;
        }
        $array = array($i => array(
            "name" => $temp->{'shop'}[$i]->{'name'},
            "id" => $temp->{'shop'}[$i]->{'id'},
            "genre" => $temp->{'shop'}[$i]->{'genre'}->{'name'},
            "number" => $start+($i+1),
            //店の緯度経度を返す
            "latitude" => $temp->{'shop'}[$i]->{'lat'},
            "longitude" => $temp->{'shop'}[$i]->{'lng'},
        )); 
        $data_array += $array;
    }
    return [$data_array];
}

?>