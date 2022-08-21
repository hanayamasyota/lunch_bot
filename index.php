<?php
//PythonやNode.jsに変える？
//LINEのミニアプリを作る？
// load files
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/reply.php';
require_once __DIR__ . '/search.php';
require_once __DIR__ . '/database_function/database_function.php';

// テーブル名を定義
//ユーザデータテーブル名(直前に送信したデータを取り込んでおく)
define('TABLE_NAME_USERS', 'users');
//ユーザの検索結果のデータを保持する
define('TABLE_NAME_USERSHOPDATA', 'usershopdata');
//ユーザの感想テーブル名
define('TABLE_NAME_REVIEWS', 'reviews');
//レビューの内容を一時的にストックするテーブル名
define('TABLE_NAME_REVIEWSTOCK', 'reviewstock');
//店の情報テーブル名(テストで3件のみ)
define('TABLE_NAME_SHOPS', 'shops');
//個人の検索結果データ
define('TABLE_NAME_NAVIGATION', 'navigation');//未実装

//1ページ当たりの表示件数(後から変更できるように)
define('PAGE_COUNT', 5);
/*テーブルデータ(★:PRIMARY KEY, ☆:FOREIGN)
users(
    ★userid(bytea)...ユーザID
    before_send(text)...直前のメッセージ
    latitude(float)...緯度
    longitude(float)...経度
    追加
    page_num...検索結果の現在のページ数
    review_shop...レビュー中の店舗ID
    shop_range...検索件数
)
usershopdata(
    ★userid(bytea)
    page_num(integer)...検索結果の現在のページ数
    review_shop(text)...レビュー中の店舗ID
    shop_range(integer)...検索件数
)
reviews(あとから変更や削除ができるようにする。自分が書いたレビューを見れるようにする。
    ★review_no(serial)...レビューを一意にするための番号
    shopid(text)...登録された店舗のID
    userid(bytea)...登録したユーザID
    evaluation(interger)...全体の評価
    recommend(text)...おすすめメニュー
    free(text)...自由欄
)
reviewstock(レビューのデータをストックしておくテーブル、キャンセル時・コミット時には消去する)(
    userid(bytea)...ユーザID
    shopid(text)...店舗ID
    review_1(integer)...全体の評価
    review_2(text)...おすすめメニュー
    review_3(text)...自由欄
)
reviews(新)(
    userid(bytea)
    shopid(text)
    review_num(int)...レビューの順番
    review(text)
)
reviews_recommend(おすすめメニュー)
reviews_free(自由欄)
shops(
    ★shopid(text)...店舗のID
    shopname(text)...店舗名
)
navigation(お店を探すとレビューで使用)(
    ★userid(bytea)...ユーザIDと店舗IDの複合主キー
    ★shopid(text)...
    shopnum(int)...店の表示順に番号を付ける
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
                if (getUserIdCheck($event->getUserId(), TABLE_NAME_REVIEWSTOCK) != PDO::PARAM_NULL) {
                    //reset reviewstock
                    deleteUser($event->getUserId(), TABLE_NAME_REVIEWSTOCK);
                }
                $mode = 'お店のレビュー';
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
            '食べたメニューまたはおすすめのメニューを入力して下さい。');
        //shop_review_3
        } else if (getBeforeMessageByUserId($event->getUserId()) === 'shop_review_3') {
            replyTextMessage($bot, $event->getReplyToken(),
            '備考等があれば入力して下さい。ない場合は「なし」と入力してください。');
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

        else if (getBeforeMessageByUserId($event->getUserId()) === 'shop_search') {
            //件数を超えて次のページにいけないようにする
            if (strcmp($event->getText(), '次へ') == 0) {}
                $page = getDataByUserShopData($event->getUserId(), 'page_num');
                $range = getDataByUserShopData($event->getUserId(), 'shop_range');
                //検索件数/PAGE_COUNT(切り上げ)よりも高い数字にならないようにする
                if ($page < ceil($range/PAGE_COUNT)) {
                    updateUserShopData($event->getUserId(), 'page_num', ($Page+1));
                    searchShop($event->getUserId(), $bot, $event->getReplyToken(), ($page+1));
                } else {
                    replyTextMessage($bot, $event->getReplyToken(), 'これ以上次へは進めません。');
                }
            //0ページよりも前にいけないようにする
            if (strcmp($event->getText(), '前へ') == 0) {}
                $page = getDataByUserShopData($event->getUserId(), 'page_num');
                if ($page >= 1) {
                    updateUserShopData($event->getUserId(), 'page_num', ($Page-1));
                    searchShop($event->getUserId(), $bot, $event->getReplyToken(), ($page-1));
                } else {
                    replyTextMessage($bot, $event->getReplyToken(), 'これ以上前には戻れません。');
                }
        }
        
    } 

    //前のメッセージが登録されていない場合
    else {
        //searchshop
        if(strcmp($event->getText(), 'お店を探す') == 0) {
            // 登録された位置情報周辺のお店を探す
            // 位置情報が設定されているかチェック
            if(getLocationByUserId($event->getUserId()) != PDO::PARAM_NULL) {
                searchShop($event->getUserId(), $bot, $event->getReplyToken());
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
            replyTextMessage($bot, $event->getReplyToken(),
            // ユーザ登録、レビューはwebでさせる
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

function searchShop($userId, $bot, $token, $page=0) {
    $location = getLocationByUserId($userId);
    //カルーセルは5件まで
    //1ページに5店表示(現在のページはデータベースに登録？)
    $shopInfo = get_restaurant_information($location['latitude'], $location['longitude'], $page);
    $columnArray = array();
    for($i = 0; $i < count($shopInfo); $i++) {
        //for文内でnavigationテーブルへのデータ追加をする
        // registerNavigation(
        //     $event->getUserId(),
        //     $shopInfo[$i]["id"],
        //     $shopInfo[$i]["number"],
        //     $shopInfo[$i]["name"],
        //     $shopInfo[$i]["latitude"],
        //     $shopInfo[$i]["longitude"]
        // );
        $actionArray = array();
        array_push($actionArray, new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder (
            '店舗情報', $shopInfo[$i]["url"]));
        array_push($actionArray, new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder (
            'レビュー確認', 'まだ実装されていません。'));
        array_push($actionArray, new LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder (
            'レビューを書く', 'review_write_'.$shopInfo[$i]["number"].'_'.$shopInfo[$i]["id"]));
        $column = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder (
            $shopInfo[$i]["name"],
            $shopInfo[$i]["number"].'/'.$shopInfo["resultrange"].'件:'.$shopInfo[$i]["genre"],
            $shopInfo[$i]["image"],
            $actionArray
        );
        array_push($columnArray, $column);
    }
    replyCarouselTemplate($bot, $token, 'お店を探す:'.($page+1).'ページ目', $columnArray);
    updateUser($userId, 'shop_search');
    if (getDataByUserShopData($userId, 'userid') != PDO::PARAM_NULL) {
        deleteUser($userId, TABLE_NAME_USERSHOPDATA);
    }
    registerUserShopData($userId, $shopInfo["resultrange"]);
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