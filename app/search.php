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
        replyTextMessage($bot, $token, '近くに飲食店が見つかりませんでした。');
    } else {
        foreach($shopInfo as $shop) {
            //到着時間を計算する(必要なときのみ表示)
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
                // " 〇〇分",

                $shop["genre"],
                $shop["image"],
                $shop["url"],
            );
        }
    }
}
//登録済みの店を表示
function showShop($page, $userId, $bot, $token, $first) {
    //カルーセルは5件まで
    //1ページに5店表示(現在のページはデータベースに登録)
    $start = $page*5;
    $shopData = getShopDataByNavigation($userId, ($start+1));
    //shopid, shopname, shopnum, shop_lat, shop_lng, genre, image, url
    if ($shopData == PDO::PARAM_NULL) {
        error_log('エラー：飲食店のデータがありません');
    }
    $shopLength = getDataByUserShopData($userId, 'shop_length');
    $showLength = $shopLength-$start;
    if ($showLength > 5) {
        $showLength = 5;
    }

    //昼休みの時刻を取得
    $restTime = getRestTimeByUserId($userId);

    $columnArray = array();
    foreach ($shopData as $shop) {
        //urlのクエリを作成
        $data = array(
            'shopid' => $shop["shopid"],
            'shopname' => $shop["shopname"],
            'conveni' => 0,
            'now_page' => 1,
        );
        $query = http_build_query($data);

        $stayTime = getStayTime($restTime["rest_start"], $restTime["rest_end"], $shop["arrival_time"]);
        $time = $stayTime[0];
        $lunch = $stayTime[1];
        //1件ごとに表示する情報
        if ($time <= 0) {
            $time = '-';
        }
        $infoStr = $shop['shopnum']."/".$shopLength."件:".$shop['genre'].
                    "\n徒歩 " . $shop['arrival_time'].
                    "\n滞在可能時間 " . $time . "分";
        //滞在可能時間が5分以下の場合は警告
        if ($time <= 5) {
            $infoStr .= " 利用可能時間が短いです";
        }

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
            $infoStr,
            $shop['image'],
            $actionArray,
        );
        array_push($columnArray, $column);
    }

    $builder = quickReplyBuilder(($start+1).'~'.($start+5).'件目',
        new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder('他の過ごし方を探す', '戻る'),
        new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder('メインメニューに戻る', '終了')
    );
    // $quick_reply_button_builder = new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder('他の過ごし方を探す', '戻る');
    // array_push($quick_reply_buttons, new LINE\LINEBot\QuickReplyBuilder\ButtonBuilder\QuickReplyButtonBuilder($quick_reply_button_builder));
    // $quick_reply_button_builder = new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder('メインメニューに戻る', '終了');
    // array_push($quick_reply_buttons, new LINE\LINEBot\QuickReplyBuilder\ButtonBuilder\QuickReplyButtonBuilder($quick_reply_button_builder));
    // $quick_reply_message_builder = new LINE\LINEBot\QuickReplyBuilder\QuickReplyMessageBuilder($quick_reply_buttons);
    // $text_message_builder = new LINE\LINEBot\MessageBuilder\TextMessageBuilder(($start+1).'~'.($start+5).'件目', $quick_reply_message_builder);

    updateUser($userId, 'shop_search');

    if ($first == true) {
        //昼休み中かどうか判定
        $message = "5件ごとにお店を表示します\n「次へ」:次の5件を表示\n「前へ」:前の5件を表示";
        if ($lunch) {
            $message .= "\n※滞在可能時間は現在時刻から昼休みの終了時刻を基準にしています。";
        } else {
            $message .= "\n※滞在可能時間は設定された昼休みの時間を基準にしています。";
        }
        $message .= "\n\nPowered by ホットペッパー Webサービス";
        replyMultiMessage($bot, $token,
            new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($message),
            new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder(
                'お店を探す:'.($page+1).'ページ目',
                new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder($columnArray)
            ),
            $builder
        );
    } else {
        replyCarouselTemplate($bot, $token,
            'お店を探す:'.($page+1).'ページ目',
            $columnArray,
        );
        replyMultiMessage($bot, $token,
        new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder(
            'お店を探す:'.($page+1).'ページ目',
            new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder($columnArray)
        ),
        $builder
    );
    }
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

                'convenience',
                null,
                null,
            );
        }
    }
}

function showConveni($page, $userId, $bot, $token, $first) {
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
            $conveni['shopnum']."/".$shopLength."件".
            "\n徒歩" . $conveni['arrival_time'],
            SERVER_ROOT.'imgs/nuko.png',
            $actionArray,
        );
        array_push($columnArray, $column);

        $count += 1;
    }
    updateUser($userId, 'conveni_search');
    
    if ($first) {
        $message = "5件ごとにお店を表示します\n「次へ」:次の5件を表示\n「前へ」:前の5件を表示";
        replyMultiMessage($bot, $token, 
        new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($message),
        new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder(
            'お店を探す:'.($page+1).'ページ目',
            new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder($columnArray)),
        );
    } else {
        replyCarouselTemplate($bot, $token,
            'お店を探す:'.($page+1).'ページ目',
            $columnArray
        );
    }
}

function makeMapURL($org_lat, $org_lng, $dst_lat, $dst_lng) {
    $http_client = new Client();

    $org_latlng = strval($org_lat) . ',' . strval($org_lng);
    $dst_latlng = strval($dst_lat) . ',' . strval($dst_lng);

    $url = 'https:/www.google.com/maps/dir/?' . http_build_query(['api' => "1", 'origin' => $org_latlng, 'destination' => $dst_latlng]);

    return $url;
}

function searchReccomend($userId, $bot, $token, $userAmbi) {
    $recShops = getMatchByNavigation($userId, $userAmbi);
    if (!(isset($recShops))) {
        replyTextMessage($bot, $token,
        'おすすめが見つかりませんでした。'
        );
        return;
    }

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

function getStayTime($restStart, $restEnd, $walkTime) {
    // タイムゾーンを日本に
    date_default_timezone_set('Asia/Tokyo');

    // :をなくす
    $now_list = explode(':', date('H:i'));
    $start_list = explode(':', $restStart);
    $end_list = explode(":", $restEnd);
    
    // 分になおす
    $nowTime = ($now_list[0] * 60) + $now_list[1];
    $startTime = ($start_list[0] * 60) + $start_list[1];
    $endTime = ($end_list[0] * 60) + $end_list[1];

    // 徒歩でかかる往復時間(分)
    $roundTrip = intval((rtrim($walkTime, '分'))) * 2;
    
    // 現在時刻が昼休み時間内かどうか
    $stayTime = array();
    if ($nowTime >= $startTime && $nowTime <= $endTime) {
        // 時間内
        array_push($stayTime, ($endTime - $nowTime - $roundTrip), true);
    } else {
        // 時間外
        array_push($stayTime, ($endTime - $startTime - $roundTrip), false);
    }
    return $stayTime;
}

function nextPage($page, $beforeMessage, $range, $bot, $userId, $token) {
    //検索件数/PAGE_COUNT(切り上げ)よりも高い数字にならないようにする
    if ($page < ceil(floatval($range)/floatval(PAGE_COUNT))) {
        updateUserShopData($userId, 'page_num', ($page+1));
        if ($beforeMessage === 'shop_search') {
            showShop(($page+1), $userId, $bot, $token, false);
        } else if ($beforeMessage === 'conveni_search') {
            showConveni(($page+1), $userId, $bot, $token, false);
        }
    } else {
        replyTextMessage($bot, $token, 'これ以上次へは進めません。');
    }
}
function beforePage($page, $beforeMessage, $bot, $userId, $token) {
    if ($page >= 1) {
        updateUserShopData($userId, 'page_num', ($page-1));
        if ($beforeMessage === 'shop_search') {
            showShop(($page-1), $userId, $bot, $token, false);
        } else if ($beforeMessage === 'conveni_search') {
            showConveni(($page-1), $userId, $bot, $token, false);
        }
    } else {
        replyTextMessage($bot, $token, 'これ以上前には戻れません。');
    }
}
?>
