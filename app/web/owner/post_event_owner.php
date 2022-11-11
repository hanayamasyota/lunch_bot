<?php
session_start();

require_once '../../DBConnection.php';
require_once '../../database_function/genre_sql.php';
define('TABLE_NAME_GENRE', 'genre');

if (!(isset($_SESSION['email']))) {
    header('Location:owner_login.php');
}

$shopname = '';
$openDate = '';
$closeDate = '';
$openTime = '';
$closeTime = '';
$lat = 0.0;
$lng = 0.0;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lat = floatval($_POST['lat']);
    $lng = floatval($_POST['lng']);
} else {
    $lat = null;
    $lng = null;
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>ひるまちGO|イベント・移動店舗登録</title>
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    <!-- Bootstrap icons-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- Google fonts-->
    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,wght@0,600;1,600&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Mulish:ital,wght@0,300;0,500;0,600;0,700;1,300;1,500;1,600;1,700&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,400;1,400&amp;display=swap" rel="stylesheet" />
    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="../css/styles.css" rel="stylesheet" />
    <link href="../css/form.css" rel="stylesheet" />
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
            <p class="text-light h3">宣伝したいことを登録</p>
        </div>
    </header>



    <!-- Contents-->
    <div class="container dx-2 my-5 bg-lightnavy text-center">
        <form method="post" action="post_event_owner_confirm.php" enctype="multipart/form-data">

        <table class="table border-top border-navy align-middle mb-5 text-nowrap">
            <thead class="border border-start">フォームの入力をしてください。</th>
                <tr>
                    <th class="col-4 py-2 align-middle bg-lightbrown">
                        <div class="text-danger d-inline">*</div>イベント名
                    </th>
                    <td class="col-8 py-2 align-middle bg-white">
                        <input type="text" name="shopname" placeholder="イベント名を入力" required>
                    </td>
                </tr>
                <tr>
                    <th class="py-2 align-middle bg-lightbrown">
                        <div class="text-danger d-inline">*</div>開催日
                    </th>
                    <td class="col-8 py-2 font-weight-normal align-middle bg-white">
                        <input type="date" name="opendate" class="w-35" required>から<br>
                        <input type="date" name="closedate" class="w-35" required>まで<br>
                        ※1日だけの場合は同じ日にちを入力
                    </td>
                </tr>
                <tr>
                    <th class="col-4 py-2 align-middle bg-lightbrown">
                        <div class="text-danger d-inline">*</div>開催時間
                    </th>
                    <td class="col-8 py-2 align-middle bg-white">
                        <input type="time" name="holdstart" required>から
                        <input type="time" name="holdend" required>まで
                    </td>
                </tr>
                <tr>
                <th class="col-3 py-2 align-middle bg-lightbrown">
                    <div class="text-danger d-inline">*</div>場所
                </th>
                <td class="col-9 py-2 align-middle bg-white">
                    <!-- readonlyにしてフォームは見えないようにする予定 -->
                    <a href="../getlatlng.php?type=event">こちらのリンクから設定してください</a>
                    <input type="text" name="lat" value="<?php echo $lat; ?>" class="d-transparent" required>
                    <input type="text" name="lng"value="<?php echo $lng; ?>" class="d-transparent d-inline" required>
                </td>
                </tr>
                <tr>
                    <th class="col-4 py-5 align-middle bg-lightbrown">
                        写真
                    </th>
                    <td class="col-8 py-1 align-middle bg-white w-100 h-100">
                        <label for="input1" class="box px-2">
                            <small>+写真を選択</small>
                            <input type="file" id="input1" name="photo" class="pt-2" style="display: none; height: auto;">
                        </label><br>
                        <img id="sample1" class="w-75 h-75 py-2">
                    </td>
                </tr>
                <tr>
                    <th class="col-4 py-2 align-middle bg-lightbrown">
                        <div class="text-danger d-inline">*</div>ジャンル
                    </th>
                    <td class="col-8 py-2 align-middle bg-white">
                        <select name="genre" requierd>
                            <option hidden value="">選択してください</option>
                            <?php 
                            $genres = getAllGenres();
                            foreach ($genres as $genre) {
                            ?>
                            <option value="<?php echo $genre["genre_id"]; ?>"><?php echo $genre["genre_name"]; ?></option>
                            <?php } ?>
                            <option value="999">その他</option>
                        </select>
                        <br>
                        <input type="text" class="w-50"><br>
                        <small>セレクトボックス内にない場合は<br>その他を選択しテキストボックスに入力してください</small>
                    </td>
                </tr>
                <tr>
                    <th class="col-4 py-2 align-middle bg-lightbrown">
                        <div class="text-danger d-inline">*</div>特徴
                    </th>
                    <td class="col-8 py-2 align-middle bg-white">
                        <textarea name="feature" class="w-75" rows="5" placeholder="イベントの開催場所、詳しい日時を含め、催しの特徴を入力してください。" maxlength="200" required></textarea>
                    </td>
                </tr>
                <tr>
                    <th class="col-4 py-2 align-middle bg-lightbrown">
                        リンク
                    </th>
                    <td class="col-8 py-2 align-middle bg-white">
                        <input type="text" name="link" class="w-75" placeholder="SNSやHPのURLを貼り付け"/>
                    </td>
                </tr>
        </table>
            <input type="submit" value="投稿する">
        </form>








    <!-- Footer-->
    <footer class="bg-black text-center py-2 mt-5 fixed-bottom">
            <div class="container px-5">
                <div class="text-white-50 small">
                    <div class="mb-2">
                        ひるまちGO
                    </div>
                    <!-- <a href="#!">Privacy</a>
                    <span class="mx-1">&middot;</span>
                    <a href="#!">Terms</a>
                    <span class="mx-1">&middot;</span>
                    <a href="#!">FAQ</a> -->
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

<script>
        $("#input1").on("change", function (e) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $("#sample1").attr("src", e.target.result);
            }
            reader.readAsDataURL(e.target.files[0]);
        });

        function Switch() {
            const radio = document.getElementsByName('radio1');
            const shop = document.getElementsByClassName('shopList');
            const event = document.getElementsByClassName('eventList');
            if (radio[0].checked) {
                shop[0].style.display = '';
                shop[1].style.display = '';
                event[0].style.display = 'none';
                event[1].style.display = 'none';
                
            }
            else if (radio[1].checked) {
                shop[0].style.display = 'none';
                shop[1].style.display = 'none';
                event[0].style.display = '';
                event[1].style.display = '';
            }
            else {
                shop[0].style.display = 'none';
                shop[1].style.display = 'none';
                event[0].style.display = 'none';
                event[1].style.display = 'none';
            }
        }

        window.onload = Switch();

</script>




</html>