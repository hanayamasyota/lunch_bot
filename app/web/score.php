<?php
require_once '../../DBConnection.php';
require_once '../database_function/users_sql.php';
require_once '../database_function/legends_sql.php';

define('TABLE_NAME_USERS', 'users');
define('TABLE_NAME_USERLEGENDS', 'user_legends');
?>

<?php
$userId = '';
$score = 0;
if ($_SERVER["REQUEST_METHOD"] != "GET") {
    $userId = $_GET["userid"];
    //登録数を取得
    $score = getCountPost($userId);
    //現在の称号を取得(関数未実装)
    $now_legend = getNowLegend($userId);
    //称号のデータを取得
    $legends = getUserLegends($userId);
}
?>


<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>ひるまちGO|スコア確認</title>
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
            <p class="text-light h3">スコア確認</p>
        </div>
    </header>

    <!-- CONTENTS -->
    <div class="container dx-3 my-5 bg-lightnavy">
        <p>昼休みの過ごし方を登録した数に応じてスコアを獲得することができます。</p>
        <h1>あなたのスコアは <?php echo $score; ?> 点です</h1>
        <p>設定中の称号名がここに入ります</p>
        <?php if (!($legends == PDO::PARAM_NULL)) { //取得称号をセレクトボックスで表示 ?>
            <?php foreach ($legends as $legend) { ?>

            <?php } ?>
        <?php } else { ?>
            <p>まだ称号を獲得していません</p>
        <?php } ?>
        
        <!-- 取得ログ -->
            <?php if (!($legends == PDO::PARAM_NULL)) { //取得ログを表示する ?>
                <?php foreach ($legends as $legend) { ?>

                <?php } ?>
            <?php }?>
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