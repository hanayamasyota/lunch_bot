<?php
//web: vendor/bin/heroku-php-nginx -C nginx_app.conf

define('SERVER_ROOT', 'https://'.$_SERVER['HTTP_HOST']);

// load files
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/reply.php';
require_once __DIR__ . '/search.php';
require_once __DIR__ . '/DBConnection.php';
$pattern = __DIR__ . '/database_function/*.php';
foreach ( glob( $pattern ) as $filename )
{
    include $filename;
}

// テーブル名を定義
//ユーザデータテーブル名(直前に送信したデータを取り込んでおく)
define('TABLE_NAME_USERS', 'users');
//ユーザの検索結果のデータを保持する
define('TABLE_NAME_USERSHOPDATA', 'usershopdata');
//ユーザの感想テーブル名(更新予定)
define('TABLE_NAME_REVIEWS', 'reviews');
//店の情報テーブル名(テストで3件のみ)
define('TABLE_NAME_USERVISITEDSHOPS', 'uservisitedshops');
//個人の検索結果データ
define('TABLE_NAME_NAVIGATION', 'navigation');

//1ページ当たりの表示件数(後から変更できるように)
define('PAGE_COUNT', 5);

/*テーブルデータ(★:PRIMARY KEY, ☆:FOREIGN)
//useridを暗号化して格納しているため、外部キーとして使うことができない！
users(
    ★userid(bytea)...ユーザID
    before_send(text)...直前のメッセージ
    latitude(float)...緯度
    longitude(float)...経度
    ambience(text)...お気に入りのジャンル
    search_range(integer)...検索範囲
    rest_start(text)...休憩の始まる時間
    rest_end(text)...休憩の終わる時間
)
usershopdata(
    ☆★userid(bytea)
    page_num(integer)...検索結果の現在のページ数
    review_shop(text)...レビュー中の店舗ID
    shop_length(integer)...検索件数
)
reviews(
    ★review_no(serial)...レビュー番号
    ☆userid(bytea)
    shopid(text)
    review_num(int)...レビューの順番
    review(text)
    追加
    shopname(text)...レビュー一覧で表示させる用
    time(timestamp)...レビューした時間(新しいレビューほど評価を重くする?)
)
uservisitedshops(
    ☆★userid(bytea)...店舗
    ★shopid(text)...店舗のID
    shopname(text)...店舗名
    visittime(timestamp)...「ここに行く」ボタンを押下した時間
    shopnum(integer)
)
navigation(お店を探すとレビューで使用)(
    ☆★userid(bytea)...ユーザIDと店舗IDの複合主キー
    ★shopid(text)
    shopnum(integer)...店の表示順に番号を付ける
    shopname(text)...店名
    shop_lat(float)...店の緯度(apiから取得)
    shop_lng(float)...店の経度
    arrival_time(text)...到着予想時間
    genre(text)...ジャンル
    image(text)...画像(url)
    url(text)...ホットペッパーURL
)

*/

// アクセストークンを使いCurlHTTPClientをインスタンス化
$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
// CurlHTTPClientとシークレットを使いLINEBotをインスタンス化
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);
// LINE Messaging APIがリクエストに付与した署名を取得
$signature = $_SERVER['HTTP_' . \LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];

// signature check
try {
    $events = $bot->parseEventRequest(file_get_contents('php://input'), $signature);
} catch(\LINE\LINEBot\Exception\InvalidSignatureException $e) {
    error_log('parseEventRequest failed. InvalidSignatureException => '.var_export($e, true));
} catch(\LINE\LINEBot\Exception\UnknownEventTypeException $e) {
    error_log('parseEventRequest failed. UnknownEventTypeException => '.var_export($e, true));
} catch(\LINE\LINEBot\Exception\UnknownMessageTypeException $e) {
    error_log('parseEventRequest failed. UnknownMessageTypeException => '.var_export($e, true));
} catch(\LINE\LINEBot\Exception\InvalidEventRequestException $e) {
    error_log('parseEventRequest failed. InvalidEventRequestException => '.var_export($e, true));
}

//main//----------------------------------------------------------------
foreach ($events as $event) {

    // 位置情報メッセージ
    if ($event instanceof \LINE\LINEBot\Event\MessageEvent\LocationMessage) {
        $beforeMessage = getBeforeMessageByUserId($event->getUserId());
        if (strpos($beforeMessage, 'setting') !== false) {
            $message = '';
            //初期設定の場合
            if (strpos($beforeMessage, '_initial') !== false) {
                $messages = [
                    '位置情報を登録しました。',
                    'まだ登録していない設定がある場合はユーザ設定へ戻ってください。'
                ];
                updateUser($event->getUserId(), 'setting_initial');
            } else if (strpos($beforeMessage, '_update') !== false) {
                $messages = [
                    '位置情報を更新しました。',
                    '他に更新したい設定がある場合はユーザ設定へ戻ってください。'
                ];
                updateUser($event->getUserId(), 'setting_update');
            }
            replyButtonsTemplate($bot, $event->getReplyToken(), '位置情報設定完了', SERVER_ROOT.'/imgs/setting.png', '位置情報設定完了',
            //現在はボタンだが、リッチメニューで対応させる予定
            $messages[0].$messages[1],
            new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder(
                'ユーザ設定へ', '設定'
            ),
            new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder(
                '戻る', 'キャンセル'
            ),
            );
            // usersテーブルに緯度経度を設定
            $lat = $event->getLatitude();
            $lon = $event->getLongitude();
            updateUser($event->getUserId(), null);
            updateLocation($event->getUserId(), $lat, $lon);
        } 
        continue;
    }

    // postbackイベント
    if ($event instanceof \LINE\LINEBot\Event\PostbackEvent) {
        if (getBeforeMessageByUserId($event->getUserId()) === 'shop_search') {
            // review_write_...
            if (strpos($event->getPostbackData(), 'visited_') !== false) {
                // postbackテキストからidを抜き出す
                $shopType = 0;
                $shopId = explode('_', $event->getPostbackData())[1];
                if (!(preg_match("/J[0-9]{9}$/", $shopId))) {
                    $shopType = 1;
                }
                $shopName = explode('_', $event->getPostbackData())[2];
                $shopNum = intval(explode('_', $event->getPostbackData())[3]);
                $lat = explode('_', $event->getPostbackData())[4];
                $lng = explode('_', $event->getPostbackData())[5];
                //timestampのデータはdate関数を使って表示させる。詳しくは↓のURL。
                //https://www.php.net/manual/ja/function.date.php
                $nowTime = time()+32400;
                $nowTimeString = date('Y-m-d H:i:s', $nowTime);
                if (checkUserVisitedShops($event->getUserId(), $shopId) != PDO::PARAM_NULL) {
                    updateUserVisitedShops($event->getUserId(), $shopId, $nowTimeString);
                } else {
                    if (countVisitedShops($event->getUserId())['shopcount'] >= 10) {
                        deleteOldUserVisitedShop($event->getUserId());
                    }

                    if ($shopType == 0) {
                        registerUserVisitedShops($event->getUserId(), $shopId, $shopName, $nowTimeString, $shopNum, 0);
                    } else if ($shopType == 1) {
                        registerUserVisitedShops($event->getUserId(), $shopId, $shopName, $nowTimeString, $shopNum, 1);
                    }
                }

                $location = getLocationByUserId($userId);
                $url = makeMapURL($location["latitude"], $location["longitude"], $lat, $lng);
                replyButtonsTemplate($bot, $event->getReplyToken(),
                '道案内',
                SERVER_ROOT . '/imgs/hirumatiGO.png',
                '道案内ページ',
                "こちらから店までの道を確認できます。",
                new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder(
                    '道案内を見る'. $url),
                );
            }
        }
        continue;
    }

    // 今行っている動きをキャンセルする
    if (strcmp($event->getText(), 'キャンセル') == 0) {
        // before_sendの有無を確認、ない場合はスルー
        if ((getBeforeMessageByUserId($event->getUserId()) != PDO::PARAM_NULL) && (getBeforeMessageByUserId($event->getUserId()) != null)) {
            $mode = '';
            $beforeMessage = getBeforeMessageByUserId($event->getUserId());
            // shop_reviewを含む場合
            if (strpos($beforeMessage, 'shop_review') !== false) {
                //レビュー
                $mode = 'レビュー';
                if (strpos($beforeMessage, '_entry') !== false) {
                    //現在レビュー中の店のデータを削除
                    $shopId = getDataByUsershopdata($event->getUserId(), 'review_shop');
                    if (checkExistsReview($event->getUserId(), $shopId, 100) != PDO::PARAM_NULL) {
                        updateUserShopData($event->getUserId(), 'review_shop', null);
                    }
                    $mode .= '登録';
                } else if (strpos($beforeMessage, '_comfirm') !== false) {
                    $mode .= '確認';
                } else if (strpos($beforeMessage, '_update') !== false) {
                    $mode .= '更新';
                } else if (strpos($beforeMessage, '_delete') !== false) {
                    $mode .= '削除';
                }
            }
            // location_setを含む場合
            else if (strpos($beforeMessage, 'setting') !== false) {
                $mode = '設定';
                if (strpos($beforeMessage, '_rest') !== false) {
                    $mode .= '休憩時間の'.$mode;
                }
            }
            // shop_searchを含む場合
            else if (strpos($beforeMessage, 'shop_search') !== false) {
                updateUserShopData($event->getUserId(), 'page_num', 0);
                $mode = 'お店を探す';
            }
            // 共通部分
            updateUser($event->getUserId(), null);
            replyTextMessage($bot, $event->getReplyToken(),
            '「'.$mode.'」がキャンセルされました。');
        }

    // before_sendが設定されている場合 //
    } else if ((getBeforeMessageByUserId($event->getUserId()) != PDO::PARAM_NULL) && (getBeforeMessageByUserId($event->getUserId()) != null)) {
        $beforeMessage = getBeforeMessageByUserId($event->getUserId());
        //shop_review
        if ($beforeMessage === 'shop_review') {
            $text = $event->getText();
            //レビュー登録
            if (strcmp($text, 'レビュー登録') == 0) {
                //「ここに行く」を押した店の番号と店名の一覧を表示する
                $replyMessage = "レビューするお店の番号を下記の中から入力してください。\n\n";
                $visitedShops = getUserVisitedShopData($event->getUserId());
                foreach ($visitedShops as $visitedShop) {
                    $replyMessage .= $visitedShop['shopnum'] . ': ' . $visitedShop['shopname']."\n";
                }
                replyTextMessage($bot, $event->getReplyToken(),
                $replyMessage);
                updateUser($event->getUserId(), 'shop_review_entry');
            //レビュー確認
            } else if (strcmp($text, 'レビュー確認・編集') == 0) {
                $data = array(
                    'userid' => $event->getUserId(),
                    'now_page' => 1
                );
                    $query = http_build_query($data);
                replyButtonsTemplate($bot, $event->getReplyToken(),
                    'レビュー確認・編集',
                    SERVER_ROOT . '/imgs/hirumatiGO.png',
                    'レビュー確認・編集',
                    "レビューの確認・編集をしますか？",
                    new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder(
                        'レビューの確認・編集', SERVER_ROOT . '/web/own_review_list.php?'. $query),
                    new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder(
                        'キャンセル', 'キャンセル'),
                );
            }
        }
        if ($beforeMessage === 'shop_review_entry') {
            //navigationテーブルに番号が存在するか確認
            if (checkShopByUserVisitedShops($event->getUserId(), intval($event->getText())) != PDO::PARAM_NULL) {
                $shop = checkShopByUserVisitedShops($event->getUserId(), intval($event->getText()));

                //urlのクエリを作成
                $data = array(
                'userid' => $event->getUserId(),
                'shopid' => $shop["shopid"],
                'shopname' => $shop["shopname"],
                'now_page' => 1,
                );
                $query = http_build_query($data);

                //該当の店のレビューがすでに存在するかをチェック
                if (checkExistsReview($event->getUserId(), $shop['shopid'], 100) != PDO::PARAM_NULL) {
                    replyButtonsTemplate($bot, $event->getReplyToken(),
                        'レビュー更新確認',
                        SERVER_ROOT . '/imgs/hirumatiGO.png',
                        'レビュー更新',
                        "この店のレビューはすでに存在します。\n" . $shop['shopname'] . "のレビューを更新しますか？",
                        new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder(
                            'はい', SERVER_ROOT . '/web/review_entry.php?' . $query),
                        new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder(
                            'キャンセル', 'キャンセル'),
                    );
                } else {
                    replyButtonsTemplate($bot, $event->getReplyToken(),
                        'レビュー登録確認',
                        SERVER_ROOT . '/imgs/hirumatiGO.png',
                        'レビュー登録',
                        $shop['shopname'] . 'のレビューをしますか？',
                        new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder(
                            'はい', SERVER_ROOT . '/web/review_entry.php?' . $query),
                        new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder(
                            'キャンセル', 'キャンセル')
                    );
                    //entry review data
                    updateUserShopData($event->getUserId(),
                        'review_shop',
                        $shop['shopid']
                    );
                    updateUser($event->getUserId(), 'shop_review_entry_000');
                }
            } else {
                replyTextMessage($bot, $event->getReplyToken(),
                '店が見つかりませんでした。正しい番号を入力して下さい。');
            }
        }

        //次、前の5件表示
        else if ($beforeMessage === 'search') {
            //設定が完了しているかチェック
            $userData = checkUsers($event->getUserId());
            if ($userData == PDO::PARAM_NULL || $userData['latitude'] == null || $userData['longitude'] == null || $userData['rest_start'] == null || $userData['rest_end'] == null){
                inductionUserSetting($bot, $event->getReplyToken());
                continue;
            }

            if ($event->getText() === '1') {
                //コンビニを検索
                searchConveni($event->getUserId(), $bot, $event->getReplyToken());
                $page = getDataByUserShopData($event->getUserId(), 'page_num');
                showConveni($page, $event->getUserId(), $bot, $event->getReplyToken());
            } else if ($event->getText() === '2') {
                //飲食店を検索
                searchShop($event->getUserId(), $bot, $event->getReplyToken());
                $page = getDataByUserShopData($event->getUserId(), 'page_num');
                showShop($page, $event->getUserId(), $bot, $event->getReplyToken());
            } else if ($event->getText() === '3') {
                //イベントを検索
            } else {
                replyTextMessage($bot, $event->getReplyToken(),
                    "無効な値です。入力しなおしてください。");
                continue;
            }
        }

        else if (strpos($beforeMessage, '_search') !== false) {
            $userId = $event->getUserId();
            //件数を超えて次のページにいけないようにする
            if (strcmp($event->getText(), '次へ') == 0) {
                $page = getDataByUserShopData($userId, 'page_num');
                $range = getDataByUserShopData($userId, 'shop_length');
                //検索件数/PAGE_COUNT(切り上げ)よりも高い数字にならないようにする
                if ($page < ceil(floatval($range)/floatval(PAGE_COUNT))) {
                    updateUserShopData($userId, 'page_num', ($page+1));
                    if ($beforeMessage === 'shop_search') {
                        showShop(($page+1), $userId, $bot, $event->getReplyToken());
                    } else if ($beforeMessage === 'conveni_search') {
                        showConveni(($page+1), $userId, $bot, $event->getReplyToken());
                    }
                } else {
                    replyTextMessage($bot, $event->getReplyToken(), 'これ以上次へは進めません。');
                }
            }
            //0ページよりも前にいけないようにする
            else if (strcmp($event->getText(), '前へ') == 0) {
                $page = getDataByUserShopData($event->getUserId(), 'page_num');
                if ($page >= 1) {
                    updateUserShopData($userId, 'page_num', ($page-1));
                    if ($beforeMessage === 'shop_search') {
                        showShop(($page-1), $userId, $bot, $event->getReplyToken());
                    } else if ($beforeMessage === 'conveni_search') {
                        showConveni(($page-1), $userId, $bot, $event->getReplyToken());
                    }
                } else {
                    replyTextMessage($bot, $event->getReplyToken(), 'これ以上前には戻れません。');
                }
            }
        }
    } 

    // 前のメッセージが登録されていない場合 //
    else {
        //search
        if (strcmp($event->getText(), 'ひるまちGO') == 0) {
            //設定チェック
            replyTextMessage($bot, $event->getReplyToken(), 
            "お昼はどうしますか？\nジャンルを数字で選んでください。\n\n1:コンビニをさがす\n2:飲食店をさがす\n3:イベントをさがす");
            updateUser($event->getUserId(), 'search');

        //review
        } else if(strcmp($event->getText(), 'レビュー') == 0) {
            //設定チェック
            $userData = checkUsers($event->getUserId());
            if ($userData == PDO::PARAM_NULL || $userData['latitude'] == null || $userData['longitude'] == null || $userData['rest_start'] == null || $userData['rest_end'] == null){
                inductionUserSetting($bot, $event->getReplyToken());
            } else {
                updateUser($event->getUserId(), 'shop_review');
                replyButtonsTemplate($bot, $event->getReplyToken(), 'レビューメニュー', SERVER_ROOT.'/img/hirumatiGO.png', 'レビューメニュー',
                'レビューのメニューです。',
                new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder(
                    'レビュー登録', 'レビュー登録'),
                new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder(
                    '自分のレビュー確認・編集', 'レビュー確認・編集'),
                );
            }

        //setting
        //あいさつメッセージでユーザ設定を促す
        } else if(strcmp($event->getText(), '設定') == 0) {
            $userData = checkUsers($event->getUserId());
            $message = 'ユーザ設定メニューです。';
            if ($userData == PDO::PARAM_NULL || $userData['latitude'] == null || $userData['longitude'] == null || $userData['rest_start'] == null || $userData['rest_end'] == null){
                $message .= '初期設定の登録をお願いします。';
                createUser($event->getUserId(), 'setting_initial');
            } else {
                $message .= '更新したい設定を選んでください。';
                createUser($event->getUserId(), 'setting_update');
            }
            replyButtonsTemplate($bot, $event->getReplyToken(), 'ユーザ設定', SERVER_ROOT.'/imgs/setting.png', 'ユーザ設定',
            $message,
            // SERVER_ROOT.'/web/post_event_owner.php'
            new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder(
                '位置情報の設定・変更', 'line://nv/location'),
            new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder(
                '個人用設定(html)', SERVER_ROOT.'/web/setting.php?userid='.$event->getUserId()),
            new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder(
                'キャンセル', 'キャンセル'),
            );

        //

        //テスト用
        } else if(strcmp($event->getText(), 'ユーザ設定削除') == 0) {
            replyTextMessage($bot, $event->getReplyToken(), 'ユーザ設定を削除しました。');
            deleteUser($event->getUserId(), TABLE_NAME_USERS);
        }
    }
}
