<?php
//web: vendor/bin/heroku-php-nginx -C nginx_app.conf

use LINE\LINEBot\QuickReplyBuilder\ButtonBuilder\QuickReplyButtonBuilder;

define('SERVER_ROOT', 'https://'.$_SERVER['HTTP_HOST']);

//リッチメニュー
define('RICHMENU_PAGECHANGE', 'richmenu-d8d0b4fbf08333f26dff7cdafff15596');

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

// $richMenuBuilder = new \LINE\LINEBot\RichMenuBuilder(

// );
// $response = $bot->createRichMenu($richMenuBuilder);

//main//----------------------------------------------------------------
foreach ($events as $event) {

    // 位置情報メッセージ
    if ($event instanceof \LINE\LINEBot\Event\MessageEvent\LocationMessage) {
        $beforeMessage = getBeforeMessageByUserId($event->getUserId());
        if (strpos($beforeMessage, 'setting') !== false) {
            $messages = [
                '位置情報を登録しました。',
                '個人用設定はこちらからできます。'
            ];
            updateUser($event->getUserId(), null);
            replyButtonsTemplate($bot, $event->getReplyToken(), '位置情報設定完了', SERVER_ROOT.'/imgs/setting.png', '位置情報設定完了',
            //現在はボタンだが、リッチメニューで対応させる予定
            $messages[0].$messages[1],
            new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder(
                '個人用設定', SERVER_ROOT.'/web/setting.php?userid='.$event->getUserId()
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
        $postBackMsg = $event->getPostbackData();
        $beforeMessage = getBeforeMessageByUserId($event->getUserId());
        if ($postBackMsg === 'score') {
            replyButtonsTemplate($bot, $event->getReplyToken(), 'スコア表示ボタン', SERVER_ROOT.'/imgs/hirumatigo.png', 'スコア表示',
            "こちらのボタンからスコアを表示します。",
            new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder(
                'スコア確認', SERVER_ROOT.'/web/review_score.php?userid='.$event->getUserId()
            ),
            );
        } else if (strpos($postBackMsg, '_page') !== false) {
            if ($postBackMsg === 'next_page') {
                $page = getDataByUserShopData($event->getUserId(), 'page_num');
                $range = getDataByUserShopData($event->getUserId(), 'shop_length');
                nextPage($page, $beforeMessage, $range, $bot, $event->getUserId(), $event->getReplyToken());
            } else {
                $page = getDataByUserShopData($event->getUserId(), 'page_num');
                beforePage($page, $beforeMessage, $bot, $event->getUserId(), $event->getReplyToken());
            }
        } else if (strpos($beforeMessage, '_search') !== false) {
            if (strpos($postBackMsg, 'visited_') !== false) {
                // postbackテキストからidを抜き出す
                $shopType = 0;
                $shopId = explode('_', $postBackMsg)[1];
                if (!(preg_match("/J[0-9]{9}$/", $shopId))) {
                    $shopType = 1;
                }
                $shopName = explode('_', $postBackMsg)[2];
                $shopNum = intval(explode('_', $postBackMsg)[3]);
                $lat = explode('_', $postBackMsg)[4];
                $lng = explode('_', $postBackMsg)[5];
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
                        registerUserVisitedShops($event->getUserId(), $shopId, $shopName, $nowTimeString, ($shopNum+10), 1);
                    }
                }

                $location = getLocationByUserId($event->getUserId());
                $url = makeMapURL($location["latitude"], $location["longitude"], $lat, $lng);
                replyButtonsTemplate($bot, $event->getReplyToken(),
                '道案内',
                SERVER_ROOT . '/imgs/hirumatiGO.png',
                $shopName.'の道案内ページ',
                "こちらから店までの道を確認できます。",
                new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder(
                    '道案内を見る', $url),
                );
                //メインメニューに戻す
                updateUser($event->getUserId(), null);
                $bot->unlinkRichMenu($event->getUserId());
            }
        }
        continue;
    }

    // 今行っている動きをキャンセルする
    if (strcmp($event->getText(), '終了') == 0) {
        // before_sendの有無を確認、ない場合はスルー
        if ((getBeforeMessageByUserId($event->getUserId()) != PDO::PARAM_NULL) && (getBeforeMessageByUserId($event->getUserId()) != null)) {
            $beforeMessage = getBeforeMessageByUserId($event->getUserId());
            //searchを含む場合
            if (strpos($beforeMessage, 'search') !== false) {
                updateUserShopData($event->getUserId(), 'page_num', 0);
            }
            // 共通部分
            updateUser($event->getUserId(), null);
            replyTextMessage($bot, $event->getReplyToken(),
            'メインメニューに戻ります');
            //デフォルトのリッチメニューに変更
            $bot->unlinkRichMenu($event->getUserId());
        }

    // before_sendが設定されている場合 //
    } else if ((getBeforeMessageByUserId($event->getUserId()) != PDO::PARAM_NULL) && (getBeforeMessageByUserId($event->getUserId()) != null)) {
        $beforeMessage = getBeforeMessageByUserId($event->getUserId());
        //review
        if ($beforeMessage === 'review') {
            $text = $event->getText();
            //レビュー登録
            if (strcmp($text, 'レビュー登録') == 0) {
                //「ここに行く」を押した店の番号と店名の一覧を表示する
                $replyMessage = "過去に行った中からレビューしたいお店の番号を入力してください。\n\n";
                $visitedShops = getUserVisitedShopData($event->getUserId());
                $count = 1;
                foreach ($visitedShops as $visitedShop) {
                    if ($visitedShop["conveni"]) {
                        $replyMessage .= ($count+10) . ': ' . $visitedShop['shopname']."\n";
                    } else {
                        $replyMessage .= $count . ': ' . $visitedShop['shopname']."\n";
                    }
                    $count += 1;
                }
                replyTextMessage($bot, $event->getReplyToken(),
                $replyMessage);
                updateUser($event->getUserId(), 'review_entry');
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
        if ($beforeMessage === 'review_entry') {
            //navigationテーブルに番号が存在するか確認
            $num = intval($event->getText());
            if ($num > 10) {
                $num -= 10;
            } 
            if (checkShopByUserVisitedShops($event->getUserId(), ($num-1)) != PDO::PARAM_NULL) {
                $shop = checkShopByUserVisitedShops($event->getUserId(), ($num-1));

                //urlのクエリを作成
                $data = array(
                'userid' => $event->getUserId(),
                'shopid' => $shop["shopid"],
                'shopname' => $shop["shopname"],
                'now_page' => 1,
                );
                $query = http_build_query($data);

                //該当の店のレビューがすでに存在するかをチェック
                if (checkExistsReview($event->getUserId(), $shop['shopid'], 1) != PDO::PARAM_NULL) {
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
                    updateUser($event->getUserId(), 'review');
                }
            } else {
                replyTextMessage($bot, $event->getReplyToken(),
                '店が見つかりませんでした。正しい番号を入力して下さい。');
            }
        }

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
                showConveni($page, $event->getUserId(), $bot, $event->getReplyToken(), true);
                $response = $bot->linkRichMenu($event->getUserId(), RICHMENU_PAGECHANGE);
            } else if ($event->getText() === '2') {
                //飲食店を検索
                searchShop($event->getUserId(), $bot, $event->getReplyToken());
                $page = getDataByUserShopData($event->getUserId(), 'page_num');
                showShop($page, $event->getUserId(), $bot, $event->getReplyToken(), true);
                $response = $bot->linkRichMenu($event->getUserId(), RICHMENU_PAGECHANGE);
            } else if ($event->getText() === '3') {
                //イベントを検索
                replyTextMessage($bot, $event->getReplyToken(),
                "何を探しますか？\n1:固定店舗\n2:イベント・移動店舗\n3:場所",
                );
                updateUser($event->getUserId(), 'event');
            } else if ($event->getText() === '4') {
                //おすすめを検索
                searchShop($event->getUserId(), $bot, $event->getReplyToken());
                $userAmbi = getAmbiByUserId($event->getUserId());
                searchReccomend($event->getUserId(), $bot, $event->getReplyToken(), $userAmbi);
            } else {
                replyTextMessage($bot, $event->getReplyToken(),
                    "無効な値です。入力しなおしてください。");
                continue;
            }
        }


        else if ($beforeMessage === 'event') {
            if ($event->getText() === '1') {
                replyButtonsTemplate($bot, $event->getReplyToken(),
                '固定店舗を探す', SERVER_ROOT.'/imgs/hirumatiGO.png', '固定店舗を探す',
                '登録されている固定店舗の一覧を表示します。',
                new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder(
                    '固定店舗一覧へ', SERVER_ROOT.'/web/shop_list.php?now_page=1'),
                );
            } else if ($event->getText() === '2') {
                replyButtonsTemplate($bot, $event->getReplyToken(),
                'イベント・移動店舗を探す', SERVER_ROOT.'/imgs/hirumatiGO.png', 'イベント・移動店舗を探す',
                '登録されているイベント・移動店舗の一覧を表示します。',
                new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder(
                    'イベント・移動店舗一覧へ', SERVER_ROOT.'/web/event_list.php?now_page=1'),
                );
            } else if ($event->getText() === '3') {
                replyButtonsTemplate($bot, $event->getReplyToken(),
                '場所を探す', SERVER_ROOT.'/imgs/hirumatiGO.png', '場所を探す',
                '登録されている場所の一覧を表示します。',
                new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder(
                    '場所一覧へ', SERVER_ROOT.'/web/life_list.php?now_page=1'),
                );
            } else {
                replyTextMessage($bot, $event->getReplyToken(),
                    "無効な値です。入力しなおしてください。");
                continue;
            }
        }

        //次、前の5件表示
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
                        showShop(($page+1), $userId, $bot, $event->getReplyToken(), false);
                    } else if ($beforeMessage === 'conveni_search') {
                        showConveni(($page+1), $userId, $bot, $event->getReplyToken(), false);
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
                        showShop(($page-1), $userId, $bot, $event->getReplyToken(), false);
                    } else if ($beforeMessage === 'conveni_search') {
                        showConveni(($page-1), $userId, $bot, $event->getReplyToken(), false);
                    }
                } else {
                    replyTextMessage($bot, $event->getReplyToken(), 'これ以上前には戻れません。');
                }
            }
            else if (strcmp($event->getText(), '戻る') == 0) {
                updateUser($event->getUserId(), 'search');
                replyTextMessage($bot, $event->getReplyToken(), 
                "ジャンルを数字で選んでください。\n\n1:コンビニをさがす\n2:飲食店をさがす\n3:みんなが登録したとこを見る\n4:おすすめの店");
            }
        }
    } 

    // 前のメッセージが登録されていない場合 //
    else {
        //search
        if (strcmp($event->getText(), 'ひるまちGO') == 0) {
            //設定チェック
            replyTextMessage($bot, $event->getReplyToken(), 
            "お昼はどうしますか？\nジャンルを数字で選んでください。\n\n1:コンビニをさがす\n2:飲食店をさがす\n3:みんなが登録したとこを見る\n4:おすすめの店");
            updateUser($event->getUserId(), 'search');

        //review
        } else if(strcmp($event->getText(), 'レビュー') == 0) {
            //設定チェック
            $userData = checkUsers($event->getUserId());
            if ($userData == PDO::PARAM_NULL || $userData['latitude'] == null || $userData['longitude'] == null || $userData['rest_start'] == null || $userData['rest_end'] == null){
                inductionUserSetting($bot, $event->getReplyToken());
            } else {
                updateUser($event->getUserId(), 'review');
                replyButtonsTemplate($bot, $event->getReplyToken(), 'レビューメニュー', SERVER_ROOT.'/imgs/hirumatiGO.png', 'レビューメニュー',
                'レビューのメニューです。',
                new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder(
                    'レビュー登録', 'レビュー登録'),
                new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder(
                    '自分のレビュー確認・編集', 'レビュー確認・編集'),
                );
            }
        
        } else if (strcmp($event->getText(), '新規登録') == 0) {
            replyButtonsTemplate($bot, $event->getReplyToken(), 'レビューメニュー', SERVER_ROOT.'/imgs/hirumatiGO.png', '新規登録',
            '新しい場所や過ごし方を登録するメニューです。',
            new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder(
                '新規登録', SERVER_ROOT.'/web/post_shop_event.php?userid='.$event->getUserId()),
                //未実装
            new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder(
                '自分が登録したものを確認', '自分が登録したものを確認'),
            );

        //setting
        //あいさつメッセージでユーザ設定を促す
        } else if(strcmp($event->getText(), '設定') == 0) {
            $userData = checkUsers($event->getUserId());
            $message = 'ユーザ設定メニューです。';
            if ($userData == PDO::PARAM_NULL || $userData['latitude'] == null || $userData['longitude'] == null || $userData['rest_start'] == null || $userData['rest_end'] == null){
                $message .= '初期設定の登録をお願いします。';
                createUser($event->getUserId(), 'setting_location');
            } else {
                $message .= '更新したい設定を選んでください。';
                createUser($event->getUserId(), 'setting_location');
            }
            replyButtonsTemplate($bot, $event->getReplyToken(), '個人情報の設定', SERVER_ROOT.'/imgs/setting.png', '個人情報の設定',
            $message,
            new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder(
                '位置情報の設定・変更', 'line://nv/location'),
            new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder(
                '個人用設定', SERVER_ROOT.'/web/setting.php?userid='.$event->getUserId()),
            new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder(
                'キャンセル', 'キャンセル'),
            );

        //テスト用
        } else if(strcmp($event->getText(), 'ユーザ設定削除') == 0) {
            replyTextMessage($bot, $event->getReplyToken(), 'ユーザ設定を削除しました。');
            deleteUser($event->getUserId(), TABLE_NAME_USERS);
        //クイックリプライのテスト
        } else if(strcmp($event->getText(), 'あ') == 0) {
            $quick_reply_buttons = array();
            $quick_reply_button_builder = new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder('他の過ごし方を探す', '戻る');
            array_push($quick_reply_buttons, new LINE\LINEBot\QuickReplyBuilder\ButtonBuilder\QuickReplyButtonBuilder($quick_reply_button_builder));
            $quick_reply_button_builder = new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder('メインメニューに戻る', '終了');
            array_push($quick_reply_buttons, new LINE\LINEBot\QuickReplyBuilder\ButtonBuilder\QuickReplyButtonBuilder($quick_reply_button_builder));
            $quick_reply_message_builder = new LINE\LINEBot\QuickReplyBuilder\QuickReplyMessageBuilder($quick_reply_buttons);
            $text_message_builder = new LINE\LINEBot\MessageBuilder\TextMessageBuilder('Message', $quick_reply_message_builder);
            $response = $bot->replyMessage($event->getReplyToken(), $text_message_builder);
        }
    }
}
