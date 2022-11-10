<?php
//店データ取得
//昼休み中に利用可能な店は強調して表示させる
function getRestaurantInfomation($userId, $lat, $lon, $range=2) {
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
    $url = 'http://webservice.recruit.co.jp/hotpepper/gourmet/v1/?';
    $url .= http_build_query($query);
    $response = file_get_contents($url);

    $json = json_decode($response);
    $data_array = renderJson($userId, $json);

    return $data_array;
}

function renderJson($userId, $json) {
    $resultLength = $json->{"results"}->{"results_available"};
    if ($resultLength < 1) {
        $result = false;
        return $result;
    }
    $temp = $json->{"results"};

    if (getDataByUserShopData($userId, 'userid') != PDO::PARAM_NULL) {
        updateUserShopData($userId, 'shop_length', $resultLength);
    } else {
        registerUserShopData($userId, $resultLength);
    }

    $data_array = array();
    for ($i = 0; $i < $resultLength; $i++) {
        $array = array($i => array(
            "name" => $temp->{'shop'}[$i]->{'name'},
            "id" => $temp->{'shop'}[$i]->{'id'},
            "genre" => $temp->{'shop'}[$i]->{'genre'}->{'name'},
            "url" => $temp->{'shop'}[$i]->{'urls'}->{'pc'},
            "image" => $temp->{'shop'}[$i]->{'photo'}->{'mobile'}->{'s'},
            "number" => ($i+1),
            "latitude" => $temp->{'shop'}[$i]->{'lat'},
            "longitude" => $temp->{'shop'}[$i]->{'lng'},
        )); 
        $data_array += $array;
    }
    return $data_array;
}

//テーブルへ店を登録
function searchShop($userId, $bot, $token) {
    if (checkShopByNavigation($userId, 1) !== PDO::PARAM_NULL) {
        deleteNavigation($userId);
    }
    $location = getLocationByUserId($userId);
    $shopInfo = getRestaurantInfomation($userId, floatval($location['latitude']), floatval($location['longitude']));
    //0件だった場合に店が無かったと表示させる
    if (($shopInfo) == false) {
        replyTextMessage($bot, $token, '店が見つかりませんでした。');
    } else {
        foreach($shopInfo as $shop) {
            //到着時間を計算する(必要なときのみ表示)
            // $arrivalTime = getTimeInfo(floatval($location['latitude']), floatval($location['longitude']), $shop['latitude'], $shop['longitude']);
            //for文内でnavigationテーブルへのデータ追加をする
            
            registerNavigation(
                $userId,
                $shop["id"],
                $shop["number"],
                $shop["name"],
                floatval($shop["latitude"]),
                floatval($shop["longitude"]),

                // $arrivalTime,
                " 〇〇分",

                $shop["genre"],
                $shop["image"],
                $shop["url"],
            );
        }
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
        //urlのクエリを作成
        $data = array(
            // 'userid' => $userId,
            'shopid' => $shop["shopid"],
            'shopname' => $shop["shopname"],
            'conveni' => 0,
            'now_page' => 1,
        );
        $query = http_build_query($data);

        $actionArray = array();
        array_push($actionArray, new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder (
            '店舗情報', $shop['url']));
        array_push($actionArray, new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder (
            //みんなのレビューを表示するページへ移動
            'レビューを見る', SERVER_ROOT."/web/review_list.php?".$query));
        array_push($actionArray, new LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder (
            //おしたときにナビゲーションをしたい !
            'ここに行く!', 'visited_'.$shop['shopid'].'_'.$shop['shopname'].'_'.$shop['shopnum'].'_'.$shop['shop_lat'].'_'.$shop['shop_lng']));
        $column = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder (
            $shop['shopname'],
            //何分かかるかを表示
            $shop['shopnum'].'/'.$shopLength.'件:'.$shop['genre'] . ' 徒歩' . $shop['arrival_time'],
            $shop['image'],
            $actionArray,
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

function getConvenienceInfo($userId, $org_lat, $org_lng) {

    $http_client = new Client();
    $url = "https://maps.googleapis.com/maps/api/place/nearbysearch/json";
    $api_key = "AIzaSyC2tnzNvq7H-AGrGdPrUdSpRTIASeim0nk";

    $org_latlng = strval($org_lat) . ',' . strval($org_lng);

    try {
        $response = $http_client->request('POST', $url, [
            'headers' => [ 'application/json',
        ],
            'query' => [
                'key' => $api_key,
                'language' => 'ja',
                'location' => $org_latlng,
                'types' => 'convenience_store',
                'radius' => '300',
            ],
            'verify' => false,
        ]);
    } catch (ClientException $e) {
        throw $e;
    }
    $body = $response->getBody();
    $json = json_decode($body);


    $lat_list = [];
    $lng_list = [];
    $time_list = [];
    
    for ($i=0; $i<count($json->{"results"}); $i++) {
        $lat = $json->{"results"}[$i]->{"geometry"}->{"location"}->{"lat"};
        array_push($lat_list, $lat);
        $lng = $json->{"results"}[$i]->{"geometry"}->{"location"}->{"lng"};
        array_push($lng_list, $lng);
        $time = getTimeInfo($org_lat, $org_lng, $lat, $lng);
        array_push($time_list, $time);
    }


    $info = [];

    $count = 0;
    for ($i=0; $i<count($json->{"results"}); $i++){
        $name = $json->{"results"}[$i]->{"name"};
        $place_id = $json->{"results"}[$i]->{"place_id"};
        array_push($info, [$place_id, ($i+1), $name, $lat_list[$i], $lng_list[$i], $time_list[$i]]);
        $count = $count+1;
    }

    if (getDataByUserShopData($userId, 'userid') != PDO::PARAM_NULL) {
        updateUserShopData($userId, 'shop_length', $count);
    } else {
        registerUserShopData($userId, $count);
    }

    return $info;
}

//テーブルへ店を登録
function searchConveni($userId, $bot, $token) {
    if (checkShopByNavigation($userId, 1) !== PDO::PARAM_NULL) {
        deleteNavigation($userId);
    }
    $location = getLocationByUserId($userId);
    $conveniInfo = getConvenienceInfo($userId, floatval($location['latitude']), floatval($location['longitude']));
    //0件だった場合に店が無かったと表示させる
    if (($conveniInfo) == false) {
        replyTextMessage($bot, $token, '店が見つかりませんでした。');
    } else {
        foreach($conveniInfo as $conveni) {
            
            registerNavigation(
                $userId,
                $conveni[0],
                $conveni[1],
                $conveni[2],
                floatval($conveni[3]),
                floatval($conveni[4]),

                $conveni[5],
                // " 〇〇分",

                'convenience',
                null,
                null,
            );
        }
    }
}

function showConveni($page, $userId, $bot, $token) {
    $start = $page*5;
    
    $conveniData = getShopDataByNavigation($userId, ($start+1));
    //shopid, shopname, shopnum, shop_lat, shop_lng, genre, image, url
    if ($conveniData == PDO::PARAM_NULL) {
        error_log('エラー：店のデータがありません');
    }
    $shopLength = getDataByUserShopData($userId, 'shop_length');

    $showLength = $shopLength-$start;
    if ($showLength > 5) {
        $showLength = 5;
    }

    $columnArray = array();
    $count = 1;
    foreach ($conveniData as $conveni) {
        //urlのクエリを作成
        $data = array(
            // 'userid' => $userId,
            'shopid' => $conveni["shopid"],
            'shopname' => $conveni["shopname"],
            'conveni' => 1,
            'now_page' => 1,
        );
        $query = http_build_query($data);

        $actionArray = array();
        array_push($actionArray, new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder (
            //みんなのレビューを表示するページへ移動
            'レビューを見る', SERVER_ROOT."/web/review_list.php?".$query));
        array_push($actionArray, new LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder (
            //おしたときにナビゲーションをしたい !
            'ここに行く!', 'visited_'.$conveni['shopid'].'_'.$conveni['shopname'].'_'.$conveni['shopnum'].'_'.$conveni['shop_lat'].'_'.$conveni['shop_lng']));
        $column = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder (
            $conveni["shopname"],
            $conveni['shopnum'].'/'.$shopLength.'件: 徒歩' . $conveni['arrival_time'],
            SERVER_ROOT.'imgs/nuko.png',
            $actionArray,
        );
        array_push($columnArray, $column);

        $count += 1;
    }
    updateUser($userId, 'conveni_search');
    
    
    replyCarouselTemplate($bot, $token, 'コンビニを探す:'.($page+1).'ページ目', $columnArray);
}

function makeMapURL($org_lat, $org_lng, $dst_lat, $dst_lng) {
    $http_client = new Client();

    $org_latlng = strval($org_lat) . ',' . strval($org_lng);
    $dst_latlng = strval($dst_lat) . ',' . strval($dst_lng);

    $url = 'https:/www.google.com/maps/dir/?' . http_build_query(['api' => "1", 'origin' => $org_latlng, 'destination' => $dst_latlng]);

    return $url;
}

function searchReccomend($userId, $bot, $token, $userAmbi) {
    $recShops = getRandomByNavigation($userId, $userAmbi);

    $columnArray = array();
    foreach ($recShops as $recShop) {
        //urlのクエリを作成
        $data = array(
            'shopid' => $recShop['shopid'],
            'shopname' => $recShop['shopname'],
            'conveni' => 0,
            'now_page' => 1,
        );
        $query = http_build_query($data);

        $actionArray = array();
        array_push($actionArray, new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder (
            '店舗情報', $recShop['url']));
        array_push($actionArray, new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder (
            //みんなのレビューを表示するページへ移動
            'レビューを見る', SERVER_ROOT."/web/review_list.php?".$query));
        array_push($actionArray, new LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder (
            //おしたときにナビゲーションをしたい !
            'ここに行く!', 'visited_'.$recShop['shopid'].'_'.$recShop['shopname'].'_'.$recShop['shopnum'].'_'.$recShop['shop_lat'].'_'.$recShop['shop_lng']));
        $column = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder (
            $recShop['shopname'],
            //何分かかるかを表示
            $recShop['genre'] . ' 徒歩' . $recShop['arrival_time'],
            $recShop['image'],
            $actionArray,
        );
        array_push($columnArray, $column);
    }
    replyCarouselTemplate($bot, $token, 'おすすめのお店', $columnArray);
}
?>
