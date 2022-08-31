<?php
//web: vendor/bin/heroku-php-nginx -C nginx_app.conf

define('SERVER_ROOT', 'https://'.$_SERVER['HTTP_HOST']);

// load files
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/reply.php';
require_once __DIR__ . '/search.php';
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
//useridを暗号化して格納しているため、外部キーとして使うことができない
//質問する必要あり
users(
    ★userid(bytea)...ユーザID
    before_send(text)...直前のメッセージ
    latitude(float)...緯度
    longitude(float)...経度
    追加
    favolite_genre...お気に入りのジャンル
    search_range...検索範囲
    rest_start...休憩の始まる時間
    rest_end...休憩の終わる時間
)
usershopdata(
    ☆★userid(bytea)
    page_num(integer)...検索結果の現在のページ数
    review_shop(text)...レビュー中の店舗ID
    shop_length(integer)...検索件数
)
追加
reviews(新)(
    ★review_no(serial)...レビュー番号
    ☆userid(bytea)
    shopid(text)
    review_num(int)...レビューの順番
    review(text)
)
追加
uservistedshops(
    ☆★userid(bytea)...店舗
    ★shopid(text)...店舗のID
    shopname(text)...店舗名
    shopnum
)
追加
navigation(お店を探すとレビューで使用)(
    ☆★userid(bytea)...ユーザIDと店舗IDの複合主キー
    ★shopid(text)...
    shopnum(integer)...店の表示順に番号を付ける
    shopname(text)...店名
    shop_lat(float)...店の緯度(apiから取得)
    shop_lng(float)...店の経度    
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
        if (getBeforeMessageByUserId($event->getUserId()) === 'setting_location') {
            // usersテーブルに緯度経度を設定
            $lat = $event->getLatitude();
            $lon = $event->getLongitude();
            updateLocation($event->getUserId(), $lat, $lon);
            updateUser($event->getUserId(), 'setting_rest_start');
            replyMultiMessage($bot, $event->getReplyToken(),
            new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('位置情報を登録しました。'),
            new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('続いて昼休憩(昼休み)の開始時刻を入力してください。(例12:00)'),
            );
        }
    }

    // postbackイベント
    if ($event instanceof \LINE\LINEBot\Event\PostbackEvent) {
        if (getBeforeMessageByUserId($event->getUserId()) === 'shop_search') {
            // review_write_...
            if (strpos($event->getPostbackData(), 'review_write_') !== false) {
                // postbackテキストからidを抜き出す
                $shopNum = intval(explode('_', $event->getPostbackData())[2]);
                $id = explode('_', $event->getPostbackData())[3];
                updateUserShopData($event->getUserId(), 'review_shop', $id);
                replyTextMessage($bot, $event->getReplyToken(), $shopNum);
            }
        }
        continue;
    }

    // キャンセル
    if (strcmp($event->getText(), 'キャンセル') == 0) {
        // before_sendの有無を確認、ない場合はスルー
        if ((getBeforeMessageByUserId($event->getUserId()) != PDO::PARAM_NULL) && (getBeforeMessageByUserId($event->getUserId()) != null)) {
            $mode = '';
            // shop_reviewを含む場合
            if (strpos(getBeforeMessageByUserId($event->getUserId()), 'shop_review') !== false) {
                //現在レビュー中の店を取り出す
                $shopId = getDataByUsershopdata($event->getUserId(), 'review_shop');
                if (checkExistsReview($event->getUserId(), $shopId) != PDO::PARAM_NULL) {
                    deleteReview($event->getUserId(), $shopId);
                }
                $mode = 'レビュー登録';
            }
            // location_setを含む場合
            else if (strpos(getBeforeMessageByUserId($event->getUserId()), 'location_set') !== false) {
                $mode = '位置情報の設定';
            }
            // shop_searchを含む場合
            else if (strpos(getBeforeMessageByUserId($event->getUserId()), 'shop_search') !== false) {
                $mode = 'お店を探す';
            }
            // 共通部分
            updateUser($event->getUserId(), null);
            replyTextMessage($bot, $event->getReplyToken(),
            '「'.$mode.'」がキャンセルされました。');
        }


    // レビューを書くかの場面で「はい」と送信された場合
    } else if ((getBeforeMessageByUserId($event->getUserId()) === 'shop_review_0') && (strcmp($event->getText(), 'はい') == 0)) {
        updateUser($event->getUserId(), 'shop_review_1');
    // 総合の評価を入力する場面で1~5の数字が送信された場合
    } else if ((getBeforeMessageByUserId($event->getUserId()) === 'shop_review_1') && (preg_match('/^[1-5]{1}/', $event->getText()))) {
        // insert reviews
        $shopId = getDataByUsershopdata($event->getUserId(), 'review_shop');
        registerReview($event->getUserId(), $shopId, 100, $event->getText());
        // update before_send
        updateUser($event->getUserId(), 'shop_review_2');
    // おすすめメニューを登録
    } else if ((getBeforeMessageByUserId($event->getUserId()) === 'shop_review_2')) {
        // insert reviews
        $shopId = getDataByUsershopdata($event->getUserId(), 'review_shop');
        registerReview($event->getUserId(), $shopId, 100, $event->getText());
        // update before_send
        updateUser($event->getUserId(), 'shop_review_3');
    // 自由欄を登録
    } else if ((getBeforeMessageByUserId($event->getUserId()) === 'shop_review_3')) {
        // insert reviews
        $shopId = getDataByUsershopdata($event->getUserId(), 'review_shop');
        registerReview($event->getUserId(), $shopId, 100, $event->getText());
        // update before_send
        updateUser($event->getUserId(), 'shop_review_confirm');
    }

    // usersテーブルのbefore_sendに設定されているメッセージに対する処理
    if ((getBeforeMessageByUserId($event->getUserId()) != PDO::PARAM_NULL) && (getBeforeMessageByUserId($event->getUserId()) != null)) {
        //shop_review
        if (getBeforeMessageByUserId($event->getUserId()) === 'shop_review') {
            //navigationテーブルに番号が存在するか確認
            if (checkShopByNavigation($event->getUserId(), intval($event->getText())) != PDO::PARAM_NULL) {
                $shop = checkShopByNavigation($event->getUserId(), intval($event->getText()));
                replyConfirmTemplate($bot, $event->getReplyToken(),
                'レビュー確認',
                $shop['shopname'].': この店のレビューを書きますか？',
                new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder(
                    'はい', 'はい'),
                new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder(
                    'キャンセル', 'キャンセル')
                );
                //entry review data
                updateUserShopData($event->getUserId(), 'review_shop', $shop['shopid']);
                updateUser($event->getUserId(), 'shop_review_0');
            } else {
                replyTextMessage($bot, $event->getReplyToken(),
                '店が見つかりませんでした。正しい番号を入力して下さい。お店の検索をしていない場合は先に検索をしてください。　');
            }
        //shop_review_1
        } else if (getBeforeMessageByUserId($event->getUserId()) === 'shop_review_1') {
            // ボタンは4件までしかできないので入力してもらう
            replyTextMessage($bot, $event->getReplyToken(), '総合の評価を1~5の5段階で入力してください。');
        //shop_review_2
        } else if (getBeforeMessageByUserId($event->getUserId()) === 'shop_review_2') {
            //200
            replyTextMessage($bot, $event->getReplyToken(),
            '食べたメニューまたはおすすめのメニューを入力して下さい。');
        //shop_review_3
        } else if (getBeforeMessageByUserId($event->getUserId()) === 'shop_review_3') {
            //300
            replyTextMessage($bot, $event->getReplyToken(),
            '備考等があれば入力して下さい。ない場合は「なし」と入力してください。');
        //shop_review_confirm(レビュー内容の確認)
        } else if (getBeforeMessageByUserId($event->getUserId()) === 'shop_review_confirm') {
            if (strcmp($event->getText(), 'はい') == 0) {
                replyTextMessage($bot, $event->getReplyToken(),
                'レビュー登録が完了しました。');
            } else {
                replyConfirmTemplate($bot, $event->getReplyToken(),
                'レビュー最終確認',
                'レビューを登録しますか？',
                new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder(
                    'はい', 'はい'),
                new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder(
                    'キャンセル', 'キャンセル')
                );
            }
        }

        //次、前の5件表示
        else if (getBeforeMessageByUserId($event->getUserId()) === 'shop_search') {
            //件数を超えて次のページにいけないようにする
            if (strcmp($event->getText(), '次へ') == 0) {
                $page = getDataByUserShopData($event->getUserId(), 'page_num');
                $range = getDataByUserShopData($event->getUserId(), 'shop_length');
                //検索件数/PAGE_COUNT(切り上げ)よりも高い数字にならないようにする
                if ($page <= ceil(floatval($range)/floatval(PAGE_COUNT))) {
                    updateUserShopData($event->getUserId(), 'page_num', ($page+1));
                    searchShop($event->getUserId(), $bot, $event->getReplyToken(), ($page+1));
                } else {
                    replyTextMessage($bot, $event->getReplyToken(), 'これ以上次へは進めません。');
                }
            }
            //0ページよりも前にいけないようにする
            else if (strcmp($event->getText(), '前へ') == 0) {
                $page = getDataByUserShopData($event->getUserId(), 'page_num');
                if ($page >= 1) {
                    updateUserShopData($event->getUserId(), 'page_num', ($page-1));
                    searchShop($event->getUserId(), $bot, $event->getReplyToken(), ($page-1));
                } else {
                    replyTextMessage($bot, $event->getReplyToken(), 'これ以上前には戻れません。');
                }
            }
        }

        //setting
        else if (getBeforeMessageByUserId($event->getUserId()) === 'setting_rest_start') {
            updateRestTime($event->getUserId(), 'rest_start', $event->getText());
            replyTextMessage($bot, $event->getReplyToken(), '昼休憩(昼休み)の終了時刻を入力してください。(例13:00)');
            updateUser($event->getUserId(), 'setting_rest_end');
        }
        else if (getBeforeMessageByUserId($event->getUserId()) === 'setting_rest_end') {
            updateRestTime($event->getUserId(), 'rest_end', $event->getText());
            replyTextMessage($bot, $event->getReplyToken(), 'ユーザ設定が完了しました。');
            updateUser($event->getUserId(), null);
        }
        
    } 

    //前のメッセージが登録されていない場合
    else {
        //searchshop
        if(strcmp($event->getText(), 'お店を探す') == 0) {
            //設定チェック
            $userData = checkUsers($event->getUserId());
            if ($userData == PDO::PARAM_NULL || $userData['latitude'] == null || $userData['longitude'] == null || $userData['rest_start'] == null || $userData['rest_end'] == null){
                inductionUserSetting($bot, $event->getReplyToken());
            } else {
                //店の検索
                searchShop($event->getUserId(), $bot, $event->getReplyToken());
            }

        //reviewshop
        } else if(strcmp($event->getText(), 'レビュー登録') == 0) {
            //設定チェック
            $userData = checkUsers($event->getUserId());
            if ($userData == PDO::PARAM_NULL || $userData['latitude'] == null || $userData['longitude'] == null || $userData['rest_start'] == null || $userData['rest_end'] == null){
                inductionUserSetting($bot, $event->getReplyToken());
            } else {
                $id = getUserIdCheck($event->getUserId(), TABLE_NAME_USERS);
                replyTextMessage($bot, $event->getReplyToken(),
                // ユーザ登録、レビューはwebでさせる
                'お店の検索件数の番号を入力してください');
                updateUser($event->getUserId(), 'shop_review');
            }

        //setting
        //あいさつメッセージでユーザ設定をさせる
        } else if(strcmp($event->getText(), 'ユーザ設定') == 0) {
            replyButtonsTemplate($bot, $event->getReplyToken(), 'ユーザ設定', SERVER_ROOT.'/imgs/setting.png', 'ユーザ設定',
            'ユーザの初期設定をします。まずは以下のボタンから位置情報の設定をお願いします。',
            new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder(
                '位置情報の設定・変更', 'line://nv/location'),
            new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder(
                'キャンセル', 'キャンセル'),
            );
            createUser($event->getUserId(), 'setting_location');
        //テスト用
        } else if(strcmp($event->getText(), 'ユーザ設定削除') == 0) {
            replyTextMessage($bot, $event->getReplyToken(), 'ユーザ設定を削除しました。');
            deleteUser($event->getUserId(), TABLE_NAME_USERS);
        }

    }
}

function createUser($userId, $beforeSend) {
    //if not exists userid, entry userid
    if(getUserIdCheck($userId, TABLE_NAME_USERS) === PDO::PARAM_NULL) {
        registerUser($userId, $beforeSend);
    } else {
        //if already exists, update
        updateUser($userId, $beforeSend);
    }
}

//0件だった場合に店が無かったと表示させる !
function searchShop($userId, $bot, $token, $page=0) {
    $location = getLocationByUserId($userId);
    //カルーセルは5件まで
    //1ページに5店表示(現在のページはデータベースに登録？)
    $shopInfo = get_restaurant_information($location['latitude'], $location['longitude'], $page);
    $columnArray = array();
    //現状だと5件までしかnavigationに登録できない !
    if (checkShopByNavigation($userId, 1) !== PDO::PARAM_NULL) {
        deleteNavigation($userId);
    }
    for($i = 0; $i < count($shopInfo); $i++) {
        //for文内でnavigationテーブルへのデータ追加をする
        registerNavigation(
            $userId,
            $shopInfo[$i]["id"],
            $shopInfo[$i]["number"],
            $shopInfo[$i]["name"],
            $shopInfo[$i]["latitude"],
            $shopInfo[$i]["longitude"]
        );
        $actionArray = array();
        array_push($actionArray, new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder (
            '店舗情報', $shopInfo[$i]["url"]));
        array_push($actionArray, new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder (
            //レビューページへ
            'レビューを見る', SERVER_ROOT.'/web/hello.html'));
        array_push($actionArray, new LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder (
            'ここに行く!', 'review_write_'.$shopInfo[$i]["number"].'_'.$shopInfo[$i]["id"]));
        $column = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder (
            $shopInfo[$i]["name"],
            $shopInfo[$i]["number"].'/'.$shopInfo[$i]["shoplength"].'件:'.$shopInfo[$i]["genre"],
            $shopInfo[$i]["image"],
            $actionArray
        );
        array_push($columnArray, $column);
    }
    replyCarouselTemplate($bot, $token, 'お店を探す:'.($page+1).'ページ目', $columnArray);
    updateUser($userId, 'shop_search');
    if (getDataByUserShopData($userId, 'userid') != PDO::PARAM_NULL) {
        updateUserShopData($userId, 'shop_length', $shopInfo[0]["shoplength"]);
    } else {
        registerUserShopData($userId, $shopInfo[0]["shoplength"]);
    }
}

//CLASS//-----------------------------------------------------------
// database_connection manage class
class dbConnection {
    // instance
    protected static $db;
    // constructor
    private function __construct() {

        try {
            // get connection_infomation from environmentvaliable to database
            $url = parse_url(getenv('DATABASE_URL'));
            // data_source
            $dsn = sprintf('pgsql:host=%s;dbname=%s', $url['host'], substr($url['path'], 1));
            // establish connection
            self::$db = new PDO($dsn, $url['user'], $url['pass']);
            // thrown Exception on error
            self::$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        }
        catch (PDOException $e) {
            error_log('Connection Error: ' . $e->getMessage());
        }
    }

    // singleton. if not exists instance, create new one.
    public static function getConnection() {
        if (!self::$db) {
            new dbConnection();
        }
    return self::$db;
    }
}
?>