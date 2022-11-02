<?php 
require_once '../DBConnection.php';
require_once '../database_function/eventshops_sql.php';
require_once 'list.php';

define('TABLE_NAME_EVENTSHOPS', 'eventshops');
?>

<?php
$shops = getShopsEventsData('0');
error_log('count:'.count($shops));
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>ひるまちGO|みんなが登録したお店一覧</title>
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
    <header class="mt-4">
        <div class="container px-3 pt-2 bg-imagecolor">
            <p class="text-light h3">お店を見る</p>
        </div>
    </header>


    <!-- Contents-->
    <div class="container mt-3 text-center py-5">
        <form method="post" action="#">

    <!--1.固定店舗テーブル-->
    <?php 
        foreach($shops as $shop) {
            error_log(print_r($shop, true));
    ?>
        <table class="table border-top border-navy align-middle mb-5 text-nowrap" style="table-layout: fixed; word-wrap: break-word;">

            <div class="container px-3 py-3 mb-3 bg-navy text-light h2">
                <?php echo $shop["event_name"]; ?>
            </div>

            <tr>
                <th class="col-4 py-5 align-middle bg-lightbrown">
                    写真
                </th>
                <td class="col-8 py-5 align-middle bg-white w-100 h-100">
                    <?php //fgetsでバイナリデータの取得自体はできたが表示ができない ?>
                    <img src="<?php echo 'data:image/png;base64,'.$shop["photo"].';'; ?>">
                </td>
            </tr>

            <tr>
                <th class="col-4 py-3 align-middle bg-lightbrown">
                    種類
                </th>
                <td class="col-8 py-3 align-middle bg-white">
                    実店舗(固定店舗)
                </td>
            </tr>
            
            <tr>
                <th class="col-4 py-4 align-middle bg-lightbrown">
                    開店日
                </th>
                <td class="col-8 py-4 font-weight-normal align-middle bg-white">
                    <?php echo $shop["open_date"]; ?>から
                </td>
            </tr>

            <tr>
                <th class="col-4 py-4 align-middle bg-lightbrown">
                    営業時間
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
                <td class="col-8 py-5 align-middle bg-white">
                    <?php echo $shop["feature"]; ?>
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
    ?>

        </form>
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