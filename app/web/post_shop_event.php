<?php
require_once '../DBConnection.php';
require_once '../database_function/genre_sql.php';
require_once '../database_function/users_sql.php';
require_once '../database_function/eventshops_sql.php';

define('TABLE_NAME_GENRE', 'genre');
define('TABLE_NAME_USERS', 'users');
define('TABLE_NAME_EVENTSHOPS', 'eventshops');
?>

<?php
//ページの状態
$status = 'input';

$userId = '';
$name = '';

$type = 'shop';

$openDate = '';
$openTime = '';
$closeTime = '';

$holdDateStart = '';
$holdDateEnd = '';
$holdStart = '';
$holdEnd = '';

$spendStart = '';
$spendEnd = '';

$lat = null;
$lng = null;

$genre = '';
$feature = '';
$other = '';

$pageName = '昼休みの過ごし方登録';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $userId = $_GET["userid"];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_POST["userid"];
    $name = $_POST["name"];
    $type = $_POST["radio1"];

    $map = $_POST["map"];

    $openDate = $_POST["opendate"];
    $openTime = $_POST["opentime"];
    $closeTime = $_POST["closetime"];

    $holdDateStart = $_POST["holddatestart"];
    $holdDateEnd = $_POST["holddateend"];
    $holdStart = $_POST["holdstart"];
    $holdEnd = $_POST["holdend"];

    $spendStart = $_POST["spendstart"];
    $spendEnd = $_POST["spendend"];

    $lat = floatval($_POST['lat']);
    $lng = floatval($_POST['lng']);

    $myGenre = $_POST["genre"];
    $feature = $_POST["feature"];

    if (!(isset($map))) {
        if (isset($userId) && isset($name) && isset($type) && isset($lat) && isset($lng) && isset($genre)) {
            $num = 0;
            if ($type == 'shop') {
                $num = 0;
                $openDate = $_POST["opendate"];
                $openTime = $_POST["opentime"];
                $closeTime = $_POST["closetime"];
            } else if ($type == 'event') {
                $num = 1;
                $openDate = $_POST["holddatestart"];
                $closeDate = $_POST["holddateend"];
                $openTime = $_POST["holdstart"];
                $closeTime = $_POST["holdend"];
            } else if ($type == 'life') {
                $num = 2;
                $openTime = $_POST["spendstart"];
                $closeTime = $_POST["spendend"];
            }

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
                $myGenre = $_POST['genre'];
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
            //登録日時を取得
            $nowTime = time()+32400;
            $nowTimeString = date('Y-m-d H:i:s', $nowTime);
            //登録
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
                $selectGenre,
                $feature,
                $lat,
                $lng,
                $nowTimeString,
            );

            //ユーザの登録回数を増やす
            countupPost($userId);

            $status = 'success';
            $pageName = '登録完了';
        } else {
            $status = 'error';
        }
    }
} else {
    $userId = $_GET["userid"];
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
    <title>ひるまちGO|<?php echo $pageName; ?></title>
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
    <link href="css/form.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.1.js" integrity="sha256-3zlB5s2uwoUzrXK3BT7AX3FyvojsraNFxCc2vC/7pNI=" crossorigin="anonymous"></script>
</head>

<body id="page-top" class="bg-base">
    <!-- Navigation-->
    <nav class="fixed-top shadow-sm" id="mainNav">
        <div class="container px-5">
            <h1 class="d-inline pt-3 font-nicokaku pe-1">ひるまち</h1>
            <h1 class="d-inline pt-3 font-rc">GO</h1>
        </div>
    </nav>
    <!-- Mashead header-->
    <header class="mt-4">
        <div class="container px-3 pt-5 bg-imagecolor">
            <p class="text-light h3">昼休みの過ごし方登録</p>
        </div>
    </header>



    <!-- Contents-->
    <?php if ($status == 'success') { ?>
        <div class="container dx-1 my-5 bg-lightnavy">
            <p>登録が完了しました。<br>このページを閉じてください。</p>
        </div>
    <?php } else { ?>
        <div class="container dx-1 my-5 bg-lightnavy text-center">
            <?php if ($status == 'error') { ?>
                <p class="text-danger">※必須項目が入力されていません</p>
            <?php } ?>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" value="<?php echo $userId; ?>" name="userid">
                <table class="table border-top border-navy align-middle text-center" style="table-layout: fixed;">
                    <thead class="border border-start">フォームの入力をしてください。</th>
                        <tr>
                            <th class="col-3 py-4 align-middle bg-lightbrown">
                                <div class="text-danger d-inline">*</div>名前
                            </th>
                            <td class="col-9 py-4 align-middle bg-white">
                                <input type="text" name="name" value="<?php echo $name; ?>">
                            </td>
                        </tr>

                        <form>
                            <tr>
                                <th class="col-3 py-4 align-middle bg-lightbrown">
                                    <div class="text-danger d-inline">*</div>種類
                                </th>
                                <td class="col-9 py-4 align-middle bg-white">
                                    <input class="form-check-input ms-2 text-left" type="radio" id="x" name="radio1" value="shop" onclick="Switch()" <?= $type == "shop" ? "checked" : "" ?>>
                                    <label for="x" class="form-check-label">固定店舗</label><br>
                                    <input class="form-check-input ms-2 text-left" type="radio" id="y" name="radio1" value="event" onclick="Switch()" <?= $type == "event" ? "checked" : "" ?>>
                                    <label for="y" class="form-check-label">イベント・移動店舗</label><br>
                                    <input class="form-check-input ms-2 text-left" type="radio" id="z" name="radio1" value="life" onclick="Switch()" <?= $type == "life" ? "checked" : "" ?>>
                                    <label for="z" class="form-check-label">過ごし方</label>
                                </td>
                            </tr>

                            <tr class="shopList">
                                <th class="col-3 py-4 align-middle bg-lightbrown">
                                    開店日
                                </th>
                                <td class="col-9 py-4 align-middle bg-white">
                                    <input type="date" name="opendate" value="<?php echo $openDate; ?>">開店
                                </td>
                            </tr>
                            <tr class="shopList">
                                <th class="col-3 py-4 align-middle bg-lightbrown">
                                    営業時間
                                </th>
                                <td class="col-9 py-4 align-middle bg-white">
                                    <input type="time" name="opentime" value="<?php echo $openTime; ?>">から
                                    <input type="time" name="closetime" value="<?php echo $closeTime; ?>">まで<br>
                                    <small class="text-left">※定休日等については下の「特徴」欄に入力してください</small>
                                </td>
                            </tr>

                            <tr class="eventList">
                                <th class="col-3 py-4 align-middle bg-lightbrown">
                                    開催日
                                </th>
                                <td class="col-9 py-4 font-weight-normal align-middle bg-white">
                                    <input type="date" name="holddatestart" class="w-35" value="<?php echo $holdDateStart; ?>">から<br>
                                    <input type="date" name="holddateend" class="w-35" value="<?php echo $holdDateEnd; ?>">まで<br>
                                    ※1日だけの場合は同じ日にちを入力
                                </td>
                            </tr>
                            <tr class="eventList">
                                <th class="col-3 py-4 align-middle bg-lightbrown">
                                    開催時間
                                </th>
                                <td class="col-9 py-4 align-middle bg-white">
                                    <input type="time" name="holdstart" value="<?php echo $holdStart; ?>">から
                                    <input type="time" name="holdend" value="<?php echo $holdEnd; ?>">まで開催
                                </td>
                            </tr>

                            <tr class="lifeList">
                                <th class="col-3 py-4 align-middle bg-lightbrown">
                                    <small>そこにいた<br>時間</small>
                                </th>
                                <td class="col-9 py-4 align-middle bg-white">
                                    <input type="time" name="spendstart" value="<?php echo $spendStart; ?>">から
                                    <input type="time" name="spendend" value="<?php echo $spendEnd; ?>">まで
                                </td>
                            </tr>
                        </form>
                        <tr>
                            <th class="col-3 py-2 align-middle bg-lightbrown">
                                <div class="text-danger d-inline">*</div>場所
                            </th>
                            <td class="col-9 py-2 align-middle bg-white">
                                <input type="submit" formaction="getlatlng.php?type=user" value="位置情報の登録"><br>
                                <input type="text" name="lat" value="<?php echo $lat; ?>" class="d-transparent">
                                <input type="text" name="lng" value="<?php echo $lng; ?>" class="d-transparent d-inline"><br>
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
                            <th class="col-3 py-4 align-middle bg-lightbrown">
                                <div class="text-danger d-inline">*</div>ジャンル
                            </th>
                            <td class="col-9 py-4 align-middle bg-white">
                                <select name="genre" class="d-inline" id="select1">
                                    <option hidden value="">選択してください</option>
                                    <?php
                                    $genres = getAllGenres();
                                    foreach ($genres as $genre) {
                                    ?>
                                    <option value="<?php echo $genre["genre_id"]; ?>"><?php echo $genre["genre_name"]; ?></option>
                                    <?php } ?>
                                    <option value="0">その他</option>
                                </select>
                                <br>
                                <input type="text" name="newgenre" class="w-50" id="newgenre" value="<?php echo $other; ?>"><br>
                                <small class="text-left">セレクトボックス内にない場合はその他を<br>選択しテキストボックスに入力してください</small>
                            </td>
                        </tr>

                        <tr>
                            <th class="col-3 py-4 align-middle bg-lightbrown">
                                特徴
                            </th>
                            <td class="col-9 py-4 align-middle bg-white">
                                <textarea name="feature" class="w-75" rows="4" maxlength="200" placeholder="それはどんなところですか？特徴を入力してください。※200文字以内" value="<?php echo $feature; ?>"></textarea>
                            </td>
                        </tr>
                </table>
                <input class="text-center" type="submit" formaction="" value="投稿する">
            </form>
        </div>
    <?php } ?>
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

<script>
    $("#input1").on("change", function(e) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $("#sample1").attr("src", e.target.result);
        }
        reader.readAsDataURL(e.target.files[0]);
    });

    function Switch() {
        const radio = document.getElementsByName('radio1');
        const shop = document.getElementsByClassName('shopList');
        const event = document.getElementsByClassName('eventList');
        const life = document.getElementsByClassName('lifeList');
        if (radio[0].checked) {
            shop[0].style.display = '';
            shop[1].style.display = '';
            event[0].style.display = 'none';
            event[1].style.display = 'none';
            life[0].style.display = 'none';
            life[1].style.display = 'none';
        } else if (radio[1].checked) {
            shop[0].style.display = 'none';
            shop[1].style.display = 'none';
            event[0].style.display = '';
            event[1].style.display = '';
            life[0].style.display = 'none';
            life[1].style.display = 'none';
        } else if (radio[2].checked) {
            shop[0].style.display = 'none';
            shop[1].style.display = 'none';
            event[0].style.display = 'none';
            event[1].style.display = 'none';
            life[0].style.display = '';
            life[1].style.display = '';
        } else {
            shop[0].style.display = 'none';
            shop[1].style.display = 'none';
            event[0].style.display = 'none';
            event[1].style.display = 'none';
            life[0].style.display = 'none';
            life[1].style.display = 'none';
        }
    }

    window.onload = Switch();
</script>




</html>