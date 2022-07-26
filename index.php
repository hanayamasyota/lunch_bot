<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/reply.php';

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

    //メッセージに対する返答
    if (strcmp($event->getText(), "あ") == 0) {
        replyTextMessage($bot, $event->getReplyToken(), "こんにちは");
    } else {
        replyTextMessage($bot, $event->getReplyToken(), $event->getText());
    }
}

?>