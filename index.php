<?php
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

//useclass
use LINE\LINEBot\TemplateActionBuilder;

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
    // event is  continue(skip)
    if (!($event instanceof \LINE\LINEBot\Event\MessageEvent)) {
        error_log('Non message event has come');
        continue;
    }

    // review chancel
    if (strcmp($event->getText(), 'キャンセル') == 0) {
        updateUser($event->getUserId(), null);
        replyTextMessage($bot, $event->getReplyToken(),
        'レビューがキャンセルされました。');
        if (getUserIdCheck($event->getUserId(), TABLE_NAME_REVIEWSTOCK) != PDO::PARAM_NULL) {
            //reset reviewstock
            deleteUser($event->getUserId(), TABLE_NAME_REVIEWSTOCK);
        }
    // entry data
    } else if ((getBeforeMessageByUserId($event->getUserId()) === 'shop_review_0') && strcmp($event->getText(), 'はい')) {
        // update before_send
        updateUser($event->getUserId(), 'shop_review_1');
    } else if ((getBeforeMessageByUserId($event->getUserId()) === 'shop_review_1') && preg_match('/score_^([1-5]{1})$/', $event->getText())) {
        // insert reviewstock
        $text = $event->getText();
        //最後の文字をとり、textをintに
        $score = (int) substr($text, -1);
        registerReviewData($event->getUserId(), 'review_1', $score);
        // update before_send
        updateUser($event->getUserId(), 'shop_review_2');
    }

    //reply for before_send
    if ((getBeforeMessageByUserId($event->getUserId()) != PDO::PARAM_NULL) && (getBeforeMessageByUserId($event->getUserId()) != null)) {
        //shop_review
        if (getBeforeMessageByUserId($event->getUserId()) === 'shop_review') {
            //check exists shopid
            if (getShopNameByShopId($event->getText()) != PDO::PARAM_NULL) {
                $shop = getShopNameByShopId($event->getText());
                replyConfirmTemplate($bot, $event->getReplyToken(),
                'レビュー確認',
                $shop['shopname'].': この店のレビューを書きますか？',
                new TemplateActionBuilder\MessageTemplateActionBuilder(
                    'はい', 'はい'),
                new TemplateActionBuilder\MessageTemplateActionBuilder(
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
            //buttontemplate
            replyButtonsTemplate($bot, $event->getReplyToken(), 'レビュー点数入力', 'https://'.$_SERVER['HTTP_HOST'].'/imgs/nuko.png', 'レビュー点数',
            '総合の評価を5段階で選んで下さい。',
            new TemplateActionBuilder\MessageTemplateActionBuilder('1', 'score_1'),
            new TemplateActionBuilder\MessageTemplateActionBuilder('2', 'score_2'),
            new TemplateActionBuilder\MessageTemplateActionBuilder('3', 'score_3'),
            new TemplateActionBuilder\MessageTemplateActionBuilder('4', 'score_4'),
            new TemplateActionBuilder\MessageTemplateActionBuilder('5', 'score_5'),
            );
        //shop_review_2
        } else if (getBeforeMessageByUserId($event->getUserId()) === 'shop_review_2') {
        
        //shop_review_3
        } else if (getBeforeMessageByUserId($event->getUserId()) === 'shop_review_3') {

        }
    } 
    // reply for message
    else {
        //searchshop
        if(strcmp($event->getText(), 'お店を探す') == 0) {
            // temporary location
            // location already setting
            // if($location = getLocationByUserId($event->getUserId()) != PDO::PARAM_NULL) {
            //     $lat = $location['latitude'];
            //     $lon = $location['longitude'];
            //     $restaurant_information = get_restaurant_information($lat, $lon);
            //     replyTextMessage($bot, $event->getReplyToken(), $restaurant_information);
            // } else {
            //     replyButtonsTemplate($bot, $event->getReplyToken(), '', '', '',
            //     '位置情報が設定されていません。位置情報の設定をお願いします。',
            //     new TemplateActionBuilder\MessageTemplateActionBuilder('位置情報の設定へ', '位置情報の設定'),
            //     );
            // }
            $lat = 36.063513;
            $lon = 136.222748;
            $restaurant_information = get_restaurant_information($lat, $lon);
            replyTextMessage($bot, $event->getReplyToken(), $restaurant_information);

        //reviewshop
        }else if(strcmp($event->getText(), 'お店のレビュー') == 0) {
            //if not exists userid, entry userid
            if(getUserIdCheck($event->getUserId(), TABLE_NAME_USERS) === PDO::PARAM_NULL) {
                registerUser($event->getUserId(), 'shop_review');
            } else {
                //if already exists, update
                updateUser($event->getUserId(), 'shop_review');
            }
            replyTextMessage($bot, $event->getReplyToken(),
            'お店のレビューをします。まずはお店のIDを入力して下さい。(IDは「お店を探す」で出てくるID欄を貼り付けて下さい。)');
        //locationset
        } else if(strcmp($event->getText(), '位置情報の設定') == 0) {
            replyButtonsTemplate($bot, $event->getReplyToken(), '位置情報の設定', 'https://'.$_SERVER['HTTP_HOST'].'/imgs/nuko.png', '位置情報の設定',
            '位置情報の設定をします。下のボタンより位置情報を送って下さい。',
            new TemplateActionBuilder\UriTemplateActionBuilder('位置情報の設定・変更', 'line://nv/location'),
            );
        }

        // test message
        // else if (strcmp($event->getText(), "あ") == 0) {
        //     replyTextMessage($bot, $event->getReplyToken(), "こんにちは");
        // } else {
        //     replyTextMessage($bot, $event->getReplyToken(), $event->getText());
        // }
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