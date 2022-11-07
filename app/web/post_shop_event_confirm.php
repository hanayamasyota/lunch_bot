<?php
require_once '../DBConnection.php';
require_once '../database_function/eventshops_sql.php';
require_once '../database_function/genre_sql.php';

define('TABLE_NAME_EVENTSHOPS', 'eventshops');
define('TABLE_NAME_GENRE', 'genre');

?>

<?php
    $userId = $_POST["userid"];
    $name = $_POST['name'];

    $openDate = null;
    $closeDate = null;
    $openTime = null;
    $closeTime = null;

    $num = 0;
    if ($_POST["radio1"] == 'shop') {
        $num=0;
        $openDate = $_POST["opendate"];
        $openTime = $_POST["opentime"];
        $closeTime = $_POST["closetime"];
    } else if ($_POST["radio1"] == 'event') {
        $num=1;
        $openDate = $_POST["holddatestart"];
        $closeDate = $_POST["holddateend"];
        $openTime = $_POST["holdstart"];
        $closeTime = $_POST["holdend"];
    } else if ($_POST["radio1"] == 'life') {
        $num=2;
        $openTime = $_POST["spendstart"];
        $closeTime = $_POST["spendend"];
    }

    $genre = '';
    //ジャンル項目でその他を選択した場合
    if ($_POST["genre"] == '0') {
        $newGenre = $_POST["newgenre"];
        if (isset($newGenre)) {
            //ジャンルがすでにあるかを確認
            $genreData = checkGenre($newGenre);
            if ($genreData != PDO::PARAM_NULL) {
                //既存のジャンルのIDで登録
                $genre = $genreData["genre_id"];
            } else {
                //新しくジャンルを登録(idはserial)
                //登録したジャンルのIDを取得
                $genreId = strval(registerGenre($newGenre));
                //新しいIDを$genreに代入
                $genre = $genreId;
            }
        }
    } else {
        $genre = $_POST['genre'];
    }
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];
    $feature = $_POST['feature'];
    $link = $_POST['link'];

    $binary_image = null;

    //一時的にファイルを保存
    if ($_FILES['photo']['size'] != 0) {
        $image = file_get_contents($_FILES['photo']['tmp_name']);
        //base64バイナリデータに変換
        $binary_image = base64_encode($image);
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        registerEventShopsByOwner(
            $userId,
            0, //オーナー
            $num, //固定店舗
            $name,
            $binary_image,
            $link,
            $openDate,
            $closeDate,
            $openTime,
            $closeTime,
            $genre,
            $feature,
            $lat,
            $lng,
        );
    }
?>


<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>ひるまちGO|昼休みの過ごし方登録完了</title>
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    <!-- Bootstrap icons-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- Google fonts-->
    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,wght@0,600;1,600&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Mulish:ital,wght@0,300;0,500;0,600;0,700;1,300;1,500;1,600;1,700&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,400;1,400&amp;display=swap" rel="stylesheet" />
    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="css/styles.css" rel="stylesheet" />
</head>

<body id="page-top" class="bg-base">
    <!-- Navigation-->
    <nav class="fixed-top shadow-sm" id="mainNav">
        <div class="container px-5">
            <h1 class="d-inline pt-3 font-nicokaku pe-1">ひるまち</h1><h1 class="d-inline pt-3 font-rc">GO</h1>
        </div>
    </nav>
    <!-- Mashead header-->
    <header class="mt-5">
        <div class="container px-3 pt-5 bg-imagecolor">
            <p class="text-light h3">登録完了</p>
        </div>
    </header>

    <!-- CONTENTS -->
    <div class="container dx-3 py-5 bg-lightnavy">
        <div class="">登録が完了しました。</div>
    </div>

    <!-- Footer-->
    <footer class="bg-black text-center py-2 fixed-bottom">
        <div class="container px-5">
            <div class="text-white-50 small">
                <div class="mb-2">
                    ひるまちGO
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap core JS-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Core theme JS-->
    <script src="js/scripts.js"></script>
    <!-- * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *-->
    <!-- * *                               SB Forms JS                               * *-->
    <!-- * * Activate your form at https://startbootstrap.com/solution/contact-forms * *-->
    <!-- * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *-->
    <script src="https://cdn.startbootstrap.com/sb-forms-latest.js"></script>
</body>

</html>