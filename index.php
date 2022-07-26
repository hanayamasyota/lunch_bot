<?php
require_once __DIR__ . '/vendor/autoload.php';
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

foreach ($events as $event) {
    // MessageEvent型でなければ処理をスキップ
    if (!($event instanceof \LINE\LINEBot\Event\MessageEvent)) {
        error_log('Non message event has come');
        continue;
    }

    //直前のメッセージに対する応答
    if (getBeforeMessageByUserId($event->getUserId()) === "shop_review") {
        if (checkExistsShopId($event->getText()) != PDO::PARAM_NULL) {
            replyTextMessage($bot, $event->getReplyToken(), "直前のメッセージを受け取りました。");
        }
    }

    //メッセージに対する返答
    if(strcmp($event->getText(), "お店のレビュー") == 0) {
        //データがない場合、ユーザデータテーブルにデータを登録
        if(getStonesByUserId($event->getUserId()) === PDO::PARAM_NULL) {
            registerUser($event->getUserId, "shop_review");
        }
        replyTextMessage($bot, $event->getReplyToken(),
        'テストでレビューを登録します。
        まずは店舗のIDを入力してください。
        （店のIDは仮で111,222,333としています。）'
        );
    }
}

//データベース関連--------------------------------------------------------------

// ユーザーIDを元にデータベースから情報を取得
function getBeforeMessageByUserId($userId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select before_send from ' . TABLE_NAME_USERS . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId));
    // レコードが存在しなければNULL
    if (!($row = $sth->fetch())) {
        return PDO::PARAM_NULL;
    } else {
        //直前のメッセージを返す
        return $row['before_send'];
    }
}

// 店舗IDを元にデータベースから情報を取得
function checkExistsShopId($shopId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select shopid, shopname from ' . TABLE_NAME_SHOPS . ' where ? = pgp_sym_decrypt(shopid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($shopId));
    // レコードが存在しなければNULL
    if (!($row = $sth->fetch())) {
        return PDO::PARAM_NULL;
    } else {
        //直前のメッセージを返す
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

//レビュー情報の登録
// function registerReview() {
//     $dbh = dbConnection::getConnection();
//     $sql = 'insert into '. TABLE_NAME_REVIEWS . ' (shopid, before_send) values (pgp_sym_encrypt(?, \'' . getenv('DB_ENCRYPT_PASS') . '\'), ?) ';
//     $sth = $dbh->prepare($sql);
//     $sth->execute(array($shopId, $));
// }

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