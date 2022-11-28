<?php
session_start();

require_once '../../DBConnection.php';
require_once '../../database_function/genre_sql.php';
require_once '../../database_function/eventshops_sql.php';

define('TABLE_NAME_GENRE', 'genre');
define('TABLE_NAME_EVENTSHOPS', 'eventshops');

if (!(isset($_SESSION['email']))) {
    header('Location:owner_login.php');
}

$status = 'input';

$userId = '';
$name = '';

$openDate = '';
$openTime = '';
$closeTime = '';

$lat = null;
$lng = null;

$genre = '';
$feature = '';
$link = '';
$other = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_POST["userid"];
    $name = $_POST["name"];
    $map = $_POST["map"];
    $openDate = $_POST["opendate"];
    $openTime = $_POST["opentime"];
    $closeTime = $_POST["closetime"];
    $spendStart = $_POST["spendstart"];
    $spendEnd = $_POST["spendend"];
    $lat = floatval($_POST['lat']);
    $lng = floatval($_POST['lng']);
    $myGenre = $_POST["genre"];
    $feature = $_POST["feature"];
    $link = $_POST["link"];

    if (!(isset($map))) {
        if (isset($userId) && isset($name) && isset($openDate) && isset($openTime) && isset($closeTime) && isset($lat) && isset($lng) && isset($myGenre) && isset($feature)) {
            $selectGenre=0;

            $selectGenre = '';
            //ジャンル項目でその他を選択した場合
            if ($myGenre == '0') {
                $newGenre = $_POST["newgenre"];
                if (isset($newGenre)) {
                    //ジャンルがすでにあるかを確認
                    $genreData = checkGenre($newGenre);
                    if ($genreData != PDO::PARAM_NULL) {
                        //既存のジャンルのIDで登録
                        $selectGenre = $genreData["genre_id"];
                    } else {
                        //新しくジャンルを登録(idはserial)
                        //登録したジャンルのIDを取得
                        $genreId = strval(registerGenre($newGenre));
                        //新しいIDを$genreに代入
                        $selectGenre = $genreId;
                    }
                }
            } else {
                $selectGenre = $_POST['genre'];
            }

            $binary_image = null;
            //一時的にファイルを保存
            if ($_FILES['photo']['size'] != 0) {
                $image = file_get_contents($_FILES['photo']['tmp_name']);
                //base64バイナリデータに変換
                $binary_image = base64_encode($image);
            }
            //登録日時を取得
            $nowTime = time()+32400;
            $nowTimeString = date('Y-m-d H:i:s', $nowTime);
            //登録
            registerEventShopsByOwner(
                $userId,
                1, //オーナー
                0, //固定店舗
                $name,
                $binary_image,
                $link,
                $openDate,
                null,
                $openTime,
                $closeTime,
                $selectGenre,
                $feature,
                $lat,
                $lng,
                $nowTimeString,
            );

            $status = 'success';
            $pageName = '登録完了';
        } else {
            $status = 'error';
        }
    }
} else {
    $userId = $_SESSION["email"];
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>ひるまちGO|</title>
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
    <?php if ($status == 'success') { ?>
        <div class="container dx-1 my-5 bg-lightnavy">
            <p>登録が完了しました。</p>
            <a href="owner_index.php">ホームへ</a>
        </div>
    <?php } else { ?>
    <div class="container dx-2 my-5 bg-lightnavy text-center">
        <?php if ($status == 'error') { ?>
            <p class="text-danger">※必須項目が入力されていません</p>
        <?php } ?>
        <form method="post" enctype="multipart/form-data">
        <input type="hidden" value="<?php echo $userId; ?>" name="userid">
        <table class="table border-top border-navy align-middle mb-4 text-nowrap" style="table-layout: fixed;">
            <thead class="border border-start">フォームの入力をしてください。</thead>
            <tr>
                <th class="col-3 py-2 align-middle bg-lightbrown">
                    <div class="text-danger d-inline">*</div>店名
                </th>
                <td class="col-9 py-2 align-middle bg-white">
                    <input type="text" name="name" value="<?php echo $name; ?>" placeholder="飲食店の名前を入力">
                </td>
            </tr>
            
            <tr>
                <th class="col-3 py-2 align-middle bg-lightbrown">
                    <div class="text-danger d-inline">*</div>開店日
                </th>
                <td class="col-9 py-2 font-weight-normal align-middle bg-white">
                    <input type="date" name="opendate" value="<?php echo $openDate; ?>" class="w-35">から
                </td>
            </tr>
            <tr>
                <th class="col-3 py-2 align-middle bg-lightbrown">
                    <div class="text-danger d-inline">*</div>営業時間
                </th>
                <td class="col-9 py-2 align-middle bg-white">
                    <input type="time" name="opentime" value="<?php echo $openTime; ?>"><div class="px-1">から</div>
                    <input type="time" name="closetime" value="<?php echo $closeTime; ?>"><div class="px-1">まで</div>
                </td>
            </tr>

            <tr>
                <th class="col-3 py-2 align-middle bg-lightbrown">
                    <div class="text-danger d-inline">*</div>場所
                </th>
                <td class="col-9 py-2 align-middle bg-white">
                    <input type="submit" formaction="../getlatlng.php?type=user" value="位置情報の登録"><br>
                    <input type="text" name="lat" value="<?php echo $lat; ?>" class="d-transparent">
                    <input type="text" name="lng"value="<?php echo $lng; ?>" class="d-transparent d-inline">
                    <div class="text-start"><small>
                        緯度：<?php echo $lat; ?><br>
                        経度：<?php echo $lng; ?>
                    </small></div>
                </td>
            </tr>

            <tr>
                <th class="col-3 py-5 align-middle bg-lightbrown">
                    写真
                </th>
                <td class="col-9 py-1 align-middle bg-white w-100 h-100">
                    <label for="input1" class="box px-2">
                        <small>+写真を選択</small>
                        <input type="file" id="input1" name="photo" class="pt-2" style="display: none;">
                    </label><br>
                    <img id="sample1" class="w-100 py-2" style="height: auto;">
                </td>
            </tr>

            <tr>
                <th class="col-3 py-2 align-middle bg-lightbrown">
                    <div class="text-danger d-inline">*</div>ジャンル
                </th>
                <td class="col-9 py-2 align-middle bg-white">
                    <select name="genre" class="d-inline" id ="select1">
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
                    <input name="other" type="text" class="w-50" id="newgenre"><br>
                    <small>セレクトボックス内にない場合は<br>その他を選択しテキストボックス<br>に追加したいジャンルを入力してください</small>
                </td>
            </tr>

            <tr>
                <th class="col-3 py-2 align-middle bg-lightbrown">
                    <div class="text-danger d-inline">*</div>特徴
                </th>
                <td class="col-9 py-2 align-middle bg-white">
                    <textarea name="feature" value="<?php echo $feature; ?>" class="w-75" rows="5" placeholder="住所や定休日などの情報を含め、お店の特徴を入力してください。※200文字以内" maxlength="200"></textarea>
                </td>
            </tr>

            <tr>
                <th class="col-3 py-2 align-middle bg-lightbrown">
                    リンク
                </th>
                <td class="col-9 py-2 align-middle bg-white">
                    <input type="text" name="link" value="<?php echo $link; ?>" class="w-75" placeholder="SNSやHPのURLを貼り付け"/>
                </td>
            </tr>
        </table>
        <input class="text-center" type="submit" formaction="" value="投稿する">
        </form>

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
    <?php } ?>
    
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

        const myGenre = document.getElementById("select1");
        myGenre.onchange = changeGenre(myGenre);

        function changeGenre(genre) {
            let num = genre.selectedIndex;
            let str = genre.options[num].value;

            if (str == '999') {

            }
        }

        function disableNewGenre() {

        }

</script>




</html>