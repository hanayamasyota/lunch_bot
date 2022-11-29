<?php 
require_once '../DBConnection.php';
require_once '../database_function/users_sql.php';
require_once '../database_function/legends_sql.php';
require_once '../database_function/eventshops_sql.php';
require_once '../database_function/genre_sql.php';
require_once 'list.php';

define('TABLE_NAME_EVENTSHOPS', 'eventshops');
define('TABLE_NAME_GENRE', 'genre');
define('TABLE_NAME_USERS', 'users');
define('TABLE_NAME_USERLEGENDS', 'user_legends');
?>

<?php
//自分が設定している場所から一定距離のものだけ表示したい
$page = $_GET["now_page"];
$maxPage = 0;
$pageRange = 0;

$shops = getShopsEventsData(2, $page);
//場所の件数
if ($shops != 0) {
    $maxPage = ceil(getDataCountByEventShops(2) / ONE_PAGE);
    $pageRange = getPageRange($page, $maxPage);
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>ひるまちGO|場所を見る</title>
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
    <link href="css/review.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.1.js" integrity="sha256-3zlB5s2uwoUzrXK3BT7AX3FyvojsraNFxCc2vC/7pNI=" crossorigin="anonymous"></script>
</head>

<body id="page-top" class="bg-base">
    <!-- Navigation-->
    <nav class="fixed-top shadow-sm" id="mainNav">
        <div class="container px-5">
            <h1 class="pt-3 font-nicokaku pe-1" style="display: inline-block;">ひるまち</h1><h1 class="pt-3 font-rc" style="display: inline-block;">GO</h1>
        </div>
    </nav>
    <!-- Mashead header-->
    <header class="mt-5">
        <div class="container px-3 pt-5 bg-imagecolor">
            <p class="text-light h3">場所を見る</p>
        </div>
    </header>

    <!-- Contents-->
    <div class="container mt-3 text-center py-5">
    <!--2.イベント・移動店舗テーブル-->
    <?php 
    if ($shops != 0) {
        foreach($shops as $shop) {
    ?>
        <div class="container px-3 py-3 mb-3 bg-navy text-light h2">
            <?php echo $shop["event_name"]; ?>
        </div>
        <table class="table border-top border-navy align-middle mb-5 text-nowrap">
            <thead>
                <div>
                    <?php
                    $userId = getUserIdByEventId($shop["event_id"]);
                    $userId = stream_get_contents($userId);
                    $now_legend = getNowLegend($userId);
                    error_log('userid='.$userId);
                    
                    $name = '';
                    ?>
                    <?php if ($shop["owner"] == true) { ?>
                        <?php 
                            $legend = getLegends($now_legend);
                            echo '<div class="bg-navy text-light d-inline px-2">オーナー</div>'; 
                        ?>
                    <?php } else if (isset($now_legend)) { ?>
                        <?php 
                            $legend = getLegends($now_legend);
                            $name = getNickNameByUserId($userId);
                            echo '<div class="bg-navy text-light d-inline px-2">'.$legend.'</div>'; 
                        ?>
                    <?php } ?>
                    <?php echo ' '.$name; ?><small>さん</small></div><?php echo "レビュー日：" . explode(' ', $shop["time"])[0]; ?>
                </div>
            </thead>

            <?php if (isset($shop["photo"])) { ?>
            <tr>
                <th class="col-4 py-5 align-middle bg-lightbrown">
                    写真
                </th>
                <td class="col-8 py-2 align-middle bg-white">
                    <img src="data:image/png;base64,<?= $shop["photo"] ?>" class="w-100" style="height: auto;">
                </td>
            </tr>
            <?php } ?>

            <?php if ((isset($shop["open_time"])) && (isset($shop["close_time"]))) { ?>
            <tr>
                <th class="col-4 py-4 align-middle bg-lightbrown">
                    過ごした時間
                </th>
                <td class="col-8 py-4 align-middle bg-white">
                    <?php echo $shop["open_time"]; ?>から
                    <?php echo $shop["close_time"]; ?>まで
                </td>
            </tr>
            <?php } ?>

            <tr>
                <th class="col-4 py-3 align-middle bg-lightbrown">
                    ジャンル
                </th>
                <td class="col-8 py-3 align-middle bg-white">
                    <?php echo getGenre($shop["genre"]); ?>
                </td>
            </tr>

            <?php if (isset($shop["feature"])) { ?>
            <tr>
                <th class="col-4 py-5 align-middle bg-lightbrown">
                    特徴
                </th>
                <td class="col-8 py-2 align-middle bg-white">
                    <textarea readonly style="resize: none; border: none;" class="w-100 h-100" rows="5"><?php echo $shop["feature"]; ?></textarea>
                </td>
            </tr>
            <?php } ?>

            <?php if (isset($shop["url"])) { ?>
            <tr>
                <th class="col-4 py-3 align-middle bg-lightbrown">
                    リンク
                </th>
                <td class="col-8 py-3 align-middle bg-white">
                    <a href="<?php echo $shop["url"]; ?>">詳細はこちら</a>
                </td>
            </tr>
            <?php } ?>
            
        </table>
    <?php } ?>
    <?php if ($reviewData != PDO::PARAM_NULL) { ?>
            <div class="pagination">
                <?php if ($page >= 2) { ?>
                    <a href="life_list.php?now_page=<?php echo $page - 1; ?>" class="page_feed">&laquo;</a>
                <?php } else { ?>
                    <span class="first_last_page">&laquo;</span>
                <?php } ?>

                <?php for ($i = 1; $i <= $maxPage; $i++) { ?>
                    <?php if (($i >= $page - $pageRange) && ($i <= $page + $pageRange)) { ?>
                        <?php if ($i == $page) { ?>
                            <span class="now_page_number"><?php echo $i; ?></span>
                        <?php } else { ?>
                            <a href="life_list.php?now_page=<?php echo $i; ?>" class="page_number"><?php echo $i; ?></a>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>

                <?php if ($page < $maxPage) { ?>
                    <a href="life_list.php?now_page=<?php echo $page + 1; ?>" class="page_feed">&raquo;</a>
                <?php } else { ?>
                    <span class="first_last_page">&raquo;</span>
                <?php } ?>
            </div>
        <?php } ?>
    <?php
    } else {
    ?>
        <div class="py-5"><p>まだ登録されていません。</p></div>
    <?php } ?>
    </div>

    <!-- Footer-->
    <footer class="bg-black text-center py-2 mt-5 fixed-bottom">
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

<?php
function getPageRange($page, $maxPage)
{
    if ($page == 1 || $page == $maxPage) {
        $range = 4;
    } elseif ($page == 2 || $page == $maxPage - 1) {
        $range = 3;
    } else {
        $range = 2;
    }
    return $range;
}
?>