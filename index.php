<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/reply.php';
require_once __DIR__ . '/search.php';
// テーブル名を定義
//ユーザデータテーブル名(直前に送信したデータを取り込んでおく)
define('TABLE_NAME_USERS', 'users');
//ユーザの感想テーブル名
define('TABLE_NAME_REVIEWS', 'reviews');
//店の情報テーブル名(テストで3件のみ)
define('TABLE_NAME_SHOPS', 'shops');
/*テーブルデータ(★:PRIMARY, ☆:FOREIGN)
users(
    ★userid(bytea)...ユーザID
    before_send(text)...直前のメッセージ
)
reviews(
    ★review_no(integer)...レビューを一意にするための番号
    ☆shopid(text)...登録された店舗のID
    ☆userid(bytea)...登録したユーザID
    evaluation(interger)...全体の評価
    free(text)...自由欄
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

// 署名チェック
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

//メイン処理
foreach ($events as $event) {
    // MessageEvent型でなければ処理をスキップ
    if (!($event instanceof \LINE\LINEBot\Event\MessageEvent)) {
        error_log('Non message event has come');
        continue;
    }

    //直前のメッセージの削除を行う
    if (strcmp($event->getText(), 'キャンセル') == 0) {
        updateUser($event->getUserId(), null);
    }

    //直前のメッセージがデータベースにある場合
    if (getBeforeMessageByUserId($event->getUserId()) != PDO::PARAM_NULL) {
        //shop_review
        if (getBeforeMessageByUserId($event->getUserId()) === 'shop_review') {
            //入力したIDの店が存在するか確認
            if (getShopNameByShopId($event->getText()) != PDO::PARAM_NULL) {
                $shopname = getShopNameByShopId($event->getText());
                replyConfirmTemplate($bot, $event->getReplyToken(),
                'レビュー確認',
                $shopname.': この店のレビューを書きますか？',
                new LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder(
                    'はい', 'cmd_review_1'),
                new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder(
                    'キャンセル', 'キャンセル')
                );
            } else {
                replyTextMessage($bot, $event->getReplyToken(),
                '店が見つかりませんでした。
                正しいIDを入力して下さい。');
            }
        }
    
    //直前のメッセージがデータベースにない場合
    } else {
        //メッセージに対する返答---------------------------------
        //お店を探す
        if(strcmp($event->getText(), 'お店を探す') == 0) {
            #データベースから位置情報取得
            #テスト用の位置情報
            $lat = 36.063513;
            $lon = 136.222748;
            $restaurant_information = get_restaurant_information($lat, $lon);
            replyTextMessage($bot, $event->getReplyToken(), $restaurant_information);
        //お店のレビュー
        }else if(strcmp($event->getText(), 'お店のレビュー') == 0) {
            //データがない場合、ユーザデータテーブルにデータを登録
            if(getBeforeMessageByUserId($event->getUserId()) === PDO::PARAM_NULL) {
                registerUser($event->getUserId(), 'shop_review');
            } else {
                //ある場合は直前のメッセージ内容を更新
                updateUser($event->getUserId(), 'shop_review');
            }
            replyTextMessage($bot, $event->getReplyToken(),
            "お店のレビューをします。
            まずはお店のIDを入力して下さい。(IDは「お店を探す」で出てくるID欄を貼り付けて下さい。)");
        }

        //メッセージに対する返答(test)
        // else if (strcmp($event->getText(), "あ") == 0) {
        //     replyTextMessage($bot, $event->getReplyToken(), "こんにちは");
        // } else {
        //     replyTextMessage($bot, $event->getReplyToken(), $event->getText());
        // }
    }
}

//データベース関連--------------------------------------------------------------

// ユーザーIDを元にデータベースから情報を取得
function getBeforeMessageByUserId($userId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select before_send from ' . TABLE_NAME_USERS . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    // $userId_bytea = pg_escape_bytea($userId);
    $sth->execute(array($userId));
    // レコードが存在しなければNULL
    if (!($row = $sth->fetch())) {
        return PDO::PARAM_NULL;
    } else {
        if ($row['before_send'] == null) {
            return PDO::PARAM_NULL;
        }
        //直前のメッセージを返す
        return $row['before_send'];
    }
}

// 店舗IDを元にデータベースから情報を取得
function getShopNameByShopId($shopId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select shopname from ' . TABLE_NAME_SHOPS . ' where ? = shopid';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($shopId));
    // レコードが存在しなければNULL
    if (!($row = $sth->fetch())) {
        return PDO::PARAM_NULL;
    } else {
        //店名を返す
        return $row['shopname'];
    }
}

// ユーザーをデータベースに登録する
function registerUser($userId, $beforeSend) {
    $dbh = dbConnection::getConnection();
    $sql = 'insert into '. TABLE_NAME_USERS . ' (userid, before_send) values (pgp_sym_encrypt(?, \'' . getenv('DB_ENCRYPT_PASS') . '\'), ?) ';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $beforeSend));
}

// ユーザ情報の更新
function updateUser($userId, $beforeSend) {
    $dbh = dbConnection::getConnection();
    $sql = 'update ' . TABLE_NAME_USERS . ' set before_send = ? where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($beforeSend, $userId));
}

// ユーザ情報の削除
function daleteUser($userId) {
    $dbh = dbConnection::getConnection();
    $sql = 'delete from ' . TABLE_NAME_USERS . ' set before_send = ? where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId));
}

//CLASS//-----------------------------------------------------------
// データベースへの接続を管理するクラス
class dbConnection {
    // インスタンス
    protected static $db;
    // コンストラクタ
    private function __construct() {

        try {
            // 環境変数からデータベースへの接続情報を取得し
            $url = parse_url(getenv('DATABASE_URL'));
            // データソース
            $dsn = sprintf('pgsql:host=%s;dbname=%s', $url['host'], substr($url['path'], 1));
            // 接続を確立
            self::$db = new PDO($dsn, $url['user'], $url['pass']);
            // エラー時例外を投げるように設定
            self::$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        }
        catch (PDOException $e) {
            error_log('Connection Error: ' . $e->getMessage());
        }
    }

    // シングルトン。存在しない場合のみインスタンス化
    public static function getConnection() {
        if (!self::$db) {
            new dbConnection();
        }
    return self::$db;
    }
}
?>