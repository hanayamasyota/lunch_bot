<?php
//店データ取得
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
            "image" => ($temp->{'shop'}[$i]->{'photo'}->{'mobile'}->{'s'}),
            "number" => ($i+1),
            "latitude" => $temp->{'shop'}[$i]->{'lat'},
            "longitude" => $temp->{'shop'}[$i]->{'lng'},
            "shoplength" => $resultLength,
        )); 
        $data_array += $array;
    }
    return $data_array;
}

//テーブルへ店を登録
function searchShop($userId, $bot, $token) {
    if (checkShopByNavigation($userId, 1) !== PDO::PARAM_NULL) {
        //
        deleteNavigation($userId);
    }
    $location = getLocationByUserId($userId);
    $shopInfo = getRestaurantInfomation(floatval($location['latitude']), floatval($location['longitude']));
    //0件だった場合に店が無かったと表示させる
    if (($shopInfo) == false) {
        replyTextMessage($bot, $token, '店が見つかりませんでした。');
    } else {
        foreach($shopInfo as $shop) {
            //到着時間を計算する
            $arrivalTime = getTimeInfo(floatval($location['latitude']), floatval($location['longitude']), $shop['latitude'], $shop['longitude']);
            //for文内でnavigationテーブルへのデータ追加をする
            registerNavigation(
                $userId,
                $shop["id"],
                $shop["number"],
                $shop["name"],
                floatval($shop["latitude"]),
                floatval($shop["longitude"]),
                $arrivalTime,
                $shop["genre"],
                $shop["image"],
                $shop["url"],
            );
        }
        if (getDataByUserShopData($userId, 'userid') != PDO::PARAM_NULL) {
            updateUserShopData($userId, 'shop_length', $shopInfo[0]["shoplength"]);
        } else {
            registerUserShopData($userId, $shopInfo[0]["shoplength"]);
        }
        error_log('店の数:'.$shopInfo[0]["shoplength"]);
    }
}
//登録済みの店を表示
function showShop($page, $userId, $bot, $token) {
    //カルーセルは5件まで
    //1ページに5店表示(現在のページはデータベースに登録)
    $start = $page*5;
    $shopData = getShopDataByNavigation($userId, ($start+1));
    //shopid, shopname, shopnum, shop_lat, shop_lng, genre, image, url
    if ($shopData == PDO::PARAM_NULL) {
        error_log('エラー：店のデータがありません');
    }
    $shopLength = getDataByUserShopData($userId, 'shop_length');

    $showLength = $shopLength-$start;
    if ($showLength > 5) {
        $showLength = 5;
    }

    $columnArray = array();
    foreach ($shopData as $shop) {
        $actionArray = array();
        array_push($actionArray, new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder (
            '店舗情報', $shop['url']));
        array_push($actionArray, new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder (
            //みんなのレビューを表示するページへ移動
            'レビューを見る', SERVER_ROOT.'/web/hello.html'));
        array_push($actionArray, new LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder (
            //店までのナビゲーションを出したい
            'ここに行く!', 'visited_'.$shop['shopid'].'_'.$shop['shopname'].'_'.$shop['shopnum']));
        $column = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder (
            $shop['shopname'],
            //何分かかるかを表示
            $shop['shopnum'].'/'.$shopLength.'件:'.$shop['genre'] . ' 徒歩' . $shop['arrival_time'],
            $shop['image'],
            $actionArray
        );
        array_push($columnArray, $column);
    }
    updateUser($userId, 'shop_search');
    replyCarouselTemplate($bot, $token, 'お店を探す:'.($page+1).'ページ目', $columnArray);
}

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

function getTimeInfo($org_lat, $org_lng, $dst_lat, $dst_lng) {
    $http_client = new Client();
    $url = 'https://maps.googleapis.com/maps/api/directions/json';
    $api_key = 'AIzaSyC2tnzNvq7H-AGrGdPrUdSpRTIASeim0nk';

    $org_latlng = strval($org_lat) . ',' . strval($org_lng);
    $dst_latlng = strval($dst_lat) . ',' . strval($dst_lng);

    try {
        $response = $http_client->request('POST', $url, [
            'headers' => [ 'application/json',
        ],
            'query' => [
                'key' => $api_key,
                'language' => 'ja',
                'origin' => $org_latlng,
                'destination' => $dst_latlng,
                'mode' => 'walking',
            ],
            'verify' => false,
        ]);
    } catch (ClientException $e) {
        throw $e;
    }
    $body = $response->getBody();
    $json = json_decode($body);

    return $json->{"routes"}[0]->{"legs"}[0]->{"duration"}->{"text"};

}


?>

