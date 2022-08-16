<?php
//次にやること：お店を探すの結果をカルーセルで返すようにする
// load files
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/reply.php';
require_once __DIR__ . '/search.php';
require_once __DIR__ . '/database_function/database_function.php';

// テーブル名を定義
//ユーザデータテーブル名(直前に送信したデータを取り込んでおく)
define('TABLE_NAME_USERS', 'users');
//ユーザの感想テーブル名
define('TABLE_NAME_REVIEWS', 'reviews');
//レビューの内容を一時的にストックするテーブル名
define('TABLE_NAME_REVIEWSTOCK', 'reviewstock');
//店の情報テーブル名(テストで3件のみ)
define('TABLE_NAME_SHOPS', 'shops');
/*テーブルデータ(★:PRIMARY, ☆:FOREIGN)
users(
    ★userid(bytea)...ユーザID
    before_send(text)...直前のメッセージ
    latitude(float)...緯度
    longitude(float)...経度
)
reviews(
    ★review_no(serial)...レビューを一意にするための番号
    ☆shopid(text)...登録された店舗のID
    ☆userid(bytea)...登録したユーザID
    evaluation(interger)...全体の評価
    recommend(text)...おすすめメニュー
    free(text)...自由欄
)
reviewstock(レビューのデータをストックしておくテーブル、キャンセル時・コミット時には消去する)(
    ☆userid(bytea)...ユーザID
    ☆shopid(text)...店舗ID
    review_1(integer)...全体の評価
    review_2(text)...おすすめメニュー
    review_3(text)...自由欄
)
shops(テスト用、実際はマップ等から選んでレビューを書けるようにする予定)(
    ★shopid(text)...店舗のID
    shopname(text)...店舗名
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
    // メッセージイベント以外はスキップ
    if (!($event instanceof \LINE\LINEBot\Event\MessageEvent)) {
        error_log('Non message event has come');
        continue;
    }

    // 位置情報メッセージ
    if ($event instanceof \LINE\LINEBot\Event\MessageEvent\LocationMessage) {
        if (getBeforeMessageByUserId($event->getUserId()) === 'location_set') {
            // usersテーブルに緯度経度を設定
            $lat = $event->getLatitude();
            $lon = $event->getLongitude();
            updateLocation($event->getUserId(), $lat, $lon);
            updateUser($event->getUserId(), null);
            replyTextMessage($bot, $event->getReplyToken(),
            '位置情報を設定しました。');
        }
    }

    // キャンセル
    if (strcmp($event->getText(), 'キャンセル') == 0) {
        // before_sendの有無を確認、ない場合はスルー
        if ((getBeforeMessageByUserId($event->getUserId()) != PDO::PARAM_NULL) && (getBeforeMessageByUserId($event->getUserId()) != null)) {
            $mode = '';

            // shop_reviewを含む場合
            if (strpos(getBeforeMessageByUserId($event->getUserId()), 'shop_review') !== false) {
                if (getUserIdCheck($event->getUserId(), TABLE_NAME_REVIEWSTOCK) != PDO::PARAM_NULL) {
                    //reset reviewstock
                    deleteUser($event->getUserId(), TABLE_NAME_REVIEWSTOCK);
                }
                $mode = 'レビュー';
            }

            // location_setを含む場合
            else if (strpos(getBeforeMessageByUserId($event->getUserId()), 'location_set') !== false) {
                $mode = '位置情報の設定';
            }
            
            // 共通部分
            updateUser($event->getUserId(), null);
            replyTextMessage($bot, $event->getReplyToken(),
            $mode.'がキャンセルされました。');
        }

    // レビューを書くかの場面で「はい」と送信された場合
    } else if ((getBeforeMessageByUserId($event->getUserId()) === 'shop_review_0') && (strcmp($event->getText(), 'はい') == 0)) {
        updateUser($event->getUserId(), 'shop_review_1');
    // 総合の評価を入力する場面で1~5の数字が送信された場合
    } else if ((getBeforeMessageByUserId($event->getUserId()) === 'shop_review_1') && (preg_match('/^[1-5]{1}/', $event->getText()))) {
        // insert reviewstock
        $evaluation = intval($event->getText());
        updateReviewData($event->getUserId(), 'review_1', $evaluation);
        // update before_send
        updateUser($event->getUserId(), 'shop_review_2');
    // おすすめメニューを仮のテーブル(reviewstock)に登録
    } else if ((getBeforeMessageByUserId($event->getUserId()) === 'shop_review_2')) {
        updateReviewData($event->getUserId(), 'review_2', $event->getText());
        updateUser($event->getUserId(), 'shop_review_3');
    // 自由欄を登録
    } else if ((getBeforeMessageByUserId($event->getUserId()) === 'shop_review_3')) {
        updateReviewData($event->getUserId(), 'review_3', $event->getText());
        updateUser($event->getUserId(), 'shop_review_confirm');
    }

    // usersテーブルのbefore_sendに設定されているメッセージに対する処理
    if ((getBeforeMessageByUserId($event->getUserId()) != PDO::PARAM_NULL) && (getBeforeMessageByUserId($event->getUserId()) != null)) {
        //shop_review
        if (getBeforeMessageByUserId($event->getUserId()) === 'shop_review') {
            //shopsテーブルにIDが存在するか確認
            if (getShopNameByShopId($event->getText()) != PDO::PARAM_NULL) {
                $shop = getShopNameByShopId($event->getText());
                replyConfirmTemplate($bot, $event->getReplyToken(),
                'レビュー確認',
                $shop['shopname'].': この店のレビューを書きますか？',
                new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder(
                    'はい', 'はい'),
                new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder(
                    'キャンセル', 'キャンセル')
                );
                //entry review data
                registerReviewDataFirst($event->getUserId(), $shop['shopid']);
                updateUser($event->getUserId(), 'shop_review_0');
            } else {
                replyTextMessage($bot, $event->getReplyToken(),
                '店が見つかりませんでした。正しいIDを入力して下さい。');
            }
        //shop_review_1
        } else if (getBeforeMessageByUserId($event->getUserId()) === 'shop_review_1') {
            // ボタンは4件までしかできないので入力してもらう
            replyTextMessage($bot, $event->getReplyToken(), '総合の評価を1~5の5段階で入力してください。');
        //shop_review_2
        } else if (getBeforeMessageByUserId($event->getUserId()) === 'shop_review_2') {
            replyTextMessage($bot, $event->getReplyToken(),
            '食べたメニューまたはおすすめのメニューを入力して下さい。(30字以下)');
        //shop_review_3
        } else if (getBeforeMessageByUserId($event->getUserId()) === 'shop_review_3') {
            replyTextMessage($bot, $event->getReplyToken(),
            '備考等があれば入力して下さい。(50字以内)ない場合は「なし」と入力してください。');
        //shop_review_confirm(レビュー内容の確認)
        } else if (getBeforeMessageByUserId($event->getUserId()) === 'shop_review_confirm') {
            if (strcmp($event->getText(), 'はい') == 0) {
                //reviewsテーブルにreviewstockテーブルのデータを入れ、reviewstockのデータを削除
                $row = getReviewStockData($event->getUserId());
                registerReview($row['shopid'], $row['userid'], $row['review_1'], $row['review_2'], $row['review_3']);
                deleteUser($event->getUserId(), TABLE_NAME_REVIEWSTOCK);
                updateUser($event->getUserId(), null);
                replyTextMessage($bot, $event->getReplyToken(),
                'レビューを登録しました。');
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
    } 

    // reply for message
    else {
        //searchshop
        if(strcmp($event->getText(), 'お店を探す') == 0) {
            // 登録された位置情報周辺のお店を探す
            // 位置情報が設定されているかチェック
            if(getLocationByUserId($event->getUserId()) != PDO::PARAM_NULL) {
                $location = getLocationByUserId($event->getUserId());
                error_log('latitude:'.$location['latitude'].',longitude:'.$location['longitude']);
                $restaurant_information = get_restaurant_information($location['latitude'], $location['longitude'], 1);
                // 今はテキストだがカルーセルで表示させたい
                replyTextMessage($bot, $event->getReplyToken(), $restaurant_information);
            } else {
                replyButtonsTemplate($bot, $event->getReplyToken(), '位置情報の設定へ', 'https://'.$_SERVER['HTTP_HOST'].'/imgs/nuko.png', '位置情報の設定へ',
                '位置情報が設定されていません。位置情報の設定をお願いします。',
                new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder('位置情報の設定へ', '位置情報の設定'),
                );
            }

        //reviewshop
        } else if(strcmp($event->getText(), 'お店のレビュー') == 0) {
            createUser($event->getUserId(), 'shop_review');
            $id = getUserIdCheck($event->getUserId(), TABLE_NAME_USERS);
            error_log('USERID:'. $id);
            replyTextMessage($bot, $event->getReplyToken(),
            'お店のレビューをします。まずはお店のIDを入力して下さい。(IDは「お店を探す」で出てくるID欄を貼り付けて下さい。)');

        //locationset
        } else if(strcmp($event->getText(), '位置情報の設定') == 0) {
            replyButtonsTemplate($bot, $event->getReplyToken(), '位置情報の設定', 'https://'.$_SERVER['HTTP_HOST'].'/imgs/nuko.png', '位置情報の設定',
            '位置情報の設定をします。下のボタンより位置情報を送って下さい。',
            new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder(
                '位置情報の設定・変更', 'line://nv/location'),
            new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder(
                'キャンセル', 'キャンセル'),
            );
            createUser($event->getUserId(), 'location_set');
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