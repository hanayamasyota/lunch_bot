<?php
//メッセージの返信ファイル

//BASE TEMPLATES-----------------------------------------------------
// テキストを返信。引数はLINEBot、返信先、テキスト
function replyTextMessage($bot, $replyToken, $text) {
    // 返信を行いレスポンスを取得
    $response = $bot->replyMessage($replyToken, new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($text));
    // レスポンスが異常な場合
    if (!$response->isSucceeded()) {
    // エラー内容を出力
        error_log('Failed! '. $response->getHTTPStatus . ' '. $response->getRawBody());
    }
}

// 位置情報を返信。引数はLINEBot、返信先、タイトル、
// 住所、緯度、経度
function replyLocationMessage($bot, $replyToken, $title, $address, $lat, $lon) {
    // LocationMessageBuilderの引数はダイアログのタイトル、
    // 住所、緯度、経度
    $response = $bot->replyMessage($replyToken, new \LINE\LINEBot\MessageBuilder\LocationMessageBuilder($title, $address, $lat, $lon));
    if (!$response->isSucceeded()) {
        error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
    }
}

// スタンプを返信。引数はLINEBot、返信先、
// スタンプのパッケージID、スタンプID
function replyStickerMessage($bot, $replyToken, $packageId, $stickerId) {
    // StickerMessageBuilderの引数はスタンプのパッケージID、スタンプID
    $response = $bot->replyMessage($replyToken,new \LINE\LINEBot\MessageBuilder\StickerMessageBuilder($packageId, $stickerId));
    if (!$response->isSucceeded()) {
        error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
    }
}

// 動画を返信。引数はLINEBot、返信先、動画URL、サムネイルURL
function replyVideoMessage($bot, $replyToken, $originalContentUrl, $previewImageUrl) {
    // VideoMessageBuilderの引数は動画URL、サムネイルURL
    $response = $bot->replyMessage($replyToken, new \LINE\LINEBot\MessageBuilder\VideoMessageBuilder($originalContentUrl, $previewImageUrl));
    if (!$response->isSucceeded()) {
        error_log('Failed! '. $response->getHTTPStatus . ' ' . $response->getRawBody());
    }
}

// オーディオファイルを返信。引数はLINEBot、返信先、
// ファイルのURL、ファイルの再生時間
function replyAudioMessage($bot, $replyToken, $originalContentUrl, $audioLength) {
    // AudioMessageBuilderの引数はファイルのURL、ファイルの再生時間
    $response = $bot->replyMessage($replyToken, new \LINE\LINEBot\MessageBuilder\AudioMessageBuilder($originalContentUrl, $audioLength));
    if (!$response->isSucceeded()) {
        error_log('Failed! '. $response->getHTTPStatus . ' ' . $response->getRawBody());
    }
}

//複数メッセージ返信。引数はLINEBot、
//返信先、メッセージ（可変長引数）
function replyMultiMessage($bot, $replyToken, ...$msgs) {
    $builder = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
    //ビルダーにメッセージをすべて追加
    foreach($msgs as $value) {
        $builder->add($value);
    }
    $response = $bot->replyMessage($replyToken, $builder);
    if (!$response->isSucceeded()) {
        error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
    }
}

//クイックリプライ送信
function quickReplyMessage($bot, $replyToken, $text, ...$actions) {
    $actionArray = array();
    foreach($actions as $value) {
        array_push($actionArray, $value);
    }
    $quick_reply_buttons = array();
    foreach ($actionArray as $action) {
        array_push($quick_reply_buttons, new LINE\LINEBot\QuickReplyBuilder\ButtonBuilder\QuickReplyButtonBuilder($action));
    }
    $quick_reply_message_builder = new LINE\LINEBot\QuickReplyBuilder\QuickReplyMessageBuilder($quick_reply_buttons);
    $text_message_builder = new LINE\LINEBot\MessageBuilder\TextMessageBuilder($text, $quick_reply_message_builder);

    $response = $bot->replyMessage($replyToken, $text_message_builder);
    if (!$response->isSucceeded()) {
        error_log('Failed! '. $response->getHTTPStatus . ' ' . $response->getRawBody());
    }
}
//ビルダー生成(マルチメッセージ用)
function quickReplyBuilder($text, ...$actions) {
    $actionArray = array();
    foreach($actions as $value) {
        array_push($actionArray, $value);
    }
    $quick_reply_buttons = array();
    foreach ($actionArray as $action) {
        array_push($quick_reply_buttons, new LINE\LINEBot\QuickReplyBuilder\ButtonBuilder\QuickReplyButtonBuilder($action));
    }
    $quick_reply_message_builder = new LINE\LINEBot\QuickReplyBuilder\QuickReplyMessageBuilder($quick_reply_buttons);
    $text_message_builder = new LINE\LINEBot\MessageBuilder\TextMessageBuilder($text, $quick_reply_message_builder);

    return $text_message_builder;
}

//Confirmテンプレート
//引数...本文、アクション
function replyConfirmTemplate($bot, $replyToken, $alternativeText, $text, ...$actions) {
    $actionArray = array();
    foreach($actions as $value) {
        array_push($actionArray, $value);
    }
    $builder = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder(
    $alternativeText,
    // Confirmテンプレートの引数はテキスト、アクションの配列
    new \LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder ($text, $actionArray));
    $response = $bot->replyMessage($replyToken, $builder);
    if (!$response->isSucceeded()) {
        error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
    }
}

//Carouselテンプレート
//引数...bot, 返信先、 代替テキスト、　ダイアログ配列
function replyCarouselTemplate($bot, $replyToken, $alternativeText, $columnArray) {
    $builder = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder(
    $alternativeText,
    // Carouselテンプレートの引数はダイアログの配列
    new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder($columnArray));
    $response = $bot->replyMessage($replyToken, $builder);
    if (!$response->isSucceeded()) {
        error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
    }
}
//-----------------------------------------------------------------

function inductionUserSetting($bot, $replyToken) {
    replyButtonsTemplate($bot, $replyToken, 'ユーザ設定へ', 'https://'.$_SERVER['HTTP_HOST'].'/imgs/setting.png', 'ユーザ設定へ',
    'ユーザ設定が完了していません。以下のボタンで設定して下さい',
    new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder(
        'ユーザ設定へ', '設定'),
    );
}