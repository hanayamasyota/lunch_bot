<?php 
require_once '../DBConnection.php';
require_once '../database_function/eventshops_sql.php';
require_once 'list.php';

define('TABLE_NAME_EVENTSHOPS', 'eventshops');
?>

<?php
$shops = getShopsEventsData(2);
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>ひるまちGO|イベントを見る</title>
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
            <p class="text-light h3">イベントを見る</p>
        </div>
    </header>

    <!-- Contents-->
    <div class="container mt-3 text-center py-5">
    <!--2.イベント・移動店舗テーブル-->
    <?php 
    if ($shops != 0) {
        foreach($shops as $shop) {
    ?>
        <table class="table border-top border-navy align-middle mb-5 text-nowrap">

            <div class="container px-3 py-3 mb-3 bg-navy text-light h2">
                <?php echo $shop["event_name"]; ?>
            </div>

            <tr>
                <th class="col-4 py-5 align-middle bg-lightbrown">
                    写真
                </th>
                <td class="col-8 py-2 align-middle bg-white">
                    <img src="data:image/png;base64,<?= $shop["photo"] ?>" class="w-100" style="height: auto;">
                </td>
            </tr>

            <tr>
                <th class="col-4 py-4 align-middle bg-lightbrown">
                    過ごした時間
                </th>
                <td class="col-8 py-4 align-middle bg-white">
                    <?php echo $shop["open_time"]; ?>から
                    <?php echo $shop["close_time"]; ?>まで
                </td>
            </tr>

            <tr>
                <th class="col-4 py-3 align-middle bg-lightbrown">
                    ジャンル
                </th>
                <td class="col-8 py-3 align-middle bg-white">
                    <?php echo GENRE_LIST[$shop["genre"]]; ?>
                </td>
            </tr>

            <tr>
                <th class="col-4 py-5 align-middle bg-lightbrown">
                    特徴
                </th>
                <td class="col-8 py-2 align-middle bg-white">
                    <textarea readonly style="resize: none; border: none;" class="w-100 h-100" rows="5"><?php echo $shop["feature"]; ?></textarea>
                </td>
            </tr>

            <tr>
                <th class="col-4 py-3 align-middle bg-lightbrown">
                    リンク
                </th>
                <td class="col-8 py-3 align-middle bg-white">
                    <?php echo $shop["url"]; ?>
                </td>
            </tr>
            
        </table>
    <?php
        }
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