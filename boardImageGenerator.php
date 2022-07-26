<?php

require_once __DIR__ . '/vendor/autoload.php';
define('GD_BASE_SIZE', 700);

$destinationImage = imagecreatefrompng('imgs/reversi_board.png');

//配列どおりに石を配置
$stones = json_decode($_REQUEST['stones']);

//列ループ
for($i = 0; $i < count($stones); $i++) {
    $row = $stones[$i];
    //要素ループ
    for($j = 0; $j < count($row); $j++) {
        //石の生成
        if($row[$j] == 1) {
            $stoneImage = imagecreatefrompng('imgs/reversi_stone_white.png');
        } elseif($row[$j] == 2) {
            $stoneImage = imagecreatefrompng('imgs/reversi_stone_black.png');
        }

        //合成
        if($row[$j] > 0) {
            imagecopy($destinationImage, $stoneImage, 9 + (int)($j * 87.5), 9 + (int)($i * 87.5), 0, 0, 70, 70);

            //破棄
            imagedestroy($stoneImage);
        }
    }
}

//リクエストサイズを取得
$size = $_REQUEST['size'];
//サイズが同じかチェック
if ($size == GD_BASE_SIZE) {
    $out = $destinationImage;
} else {
    //空画像を生成
    $out = imagecreatetruecolor($size, $size);
    //リサイズと合成
    imagecopyresampled($out, $destinationImage, 0, 0, 0, 0, $size, $size, GD_BASE_SIZE, GD_BASE_SIZE);
}
//出力のバッファリングを有効に
ob_start();
//出力
imagepng($out, null, 9);
//バッファから画像を取得
$content = ob_get_contents();
//バッファを消去、バッファリングをオフ
ob_end_clean();

//出力タイプしてい
header('Content-type: image/png');
echo $content;
?>