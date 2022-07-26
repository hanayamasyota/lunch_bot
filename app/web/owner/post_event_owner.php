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
?>

<?php
//ページの状態
$status = 'input';

$userId = null;
$name = null;

$holdDateStart = null;
$holdDateEnd = null;
$holdStart = null;
$holdEnd = null;

$lat = null;
$lng = null;

$genre = null;
$feature = null;
$link = null;
$other = null;

$pageName = 'イベントの登録';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_POST["userid"];
    $name = $_POST["name"];
    $map = $_POST["map"];
    $holdDateStart = $_POST["holddatestart"];
    $holdDateEnd = $_POST["holddateend"];
    $holdStart = $_POST["holdstart"];
    $holdEnd = $_POST["holdend"];
    $lat = floatval($_POST['lat']);
    $lng = floatval($_POST['lng']);
    $myGenre = $_POST["genre"];
    $feature = $_POST["feature"];
    $link = $_POST["link"];

    if (!(isset($map))) {
        if ((isset($userId)) && (isset($name)) && (isset($holdDateStart)) && (isset($holdDateEnd)) && (isset($holdStart)) && (isset($holdEnd)) && (isset($lat)) && (isset($lng)) && (isset($myGenre)) && (isset($feature))) {
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
            $nowTime = time() + 32400;
            $nowTimeString = date('Y-m-d H:i:s', $nowTime);
            //登録
            registerEventShopsByOwner(
                $userId,
                1, //オーナー
                1, //固定店舗
                $name,
                $binary_image,
                $link,
                $holdDateStart,
                $holdDateEnd,
                $holdStart,
                $holdEnd,
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
    <link href="../css/styles.css" rel="stylesheet" />
    <link href="../css/form.css" rel="stylesheet" />
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
    <header class="mt-5">
        <div class="container px-3 pt-5 bg-imagecolor">
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
        <div class="container px-3 py-3 mb-3 bg-navy text-light h2">
            <?php echo 'イベント・移動店舗の登録フォーム'; ?>
        </div>
            <?php if ($status == 'error') { ?>
                <p class="text-danger">※必須項目が入力されていません</p>
            <?php } ?>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" value="<?php echo $userId; ?>" name="userid">
                <table class="table border-top border-navy align-middle mb-3 text-nowrap">
                        <tr>
                            <th class="col-4 py-2 align-middle bg-lightbrown">
                                <div class="text-danger d-inline">*</div>イベント名
                            </th>
                            <td class="col-8 py-2 align-middle bg-white">
                                <input type="text" name="name" class="col-6" value="<?php echo $name; ?>" placeholder="イベント名を入力">
                            </td>
                        </tr>
                        <tr>
                            <th class="py-2 align-middle bg-lightbrown">
                                <div class="text-danger d-inline">*</div>開催日
                            </th>
                            <td class="col-8 py-2 font-weight-normal align-middle bg-white">
                                <input type="date" name="holddatestart" value="<?php echo $holdDateStart; ?>" class="col-3 me-2">から
                                <input type="date" name="holddateend" value="<?php echo $holdDateEnd; ?>" class="col-3 mx-2">まで<br>
                                ※1日だけの場合は同じ日にちを入力
                            </td>
                        </tr>
                        <tr>
                            <th class="col-4 py-2 align-middle bg-lightbrown">
                                <div class="text-danger d-inline">*</div>開催時間
                            </th>
                            <td class="col-8 py-2 align-middle bg-white text-left">
                                <input type="time" class="me-2" name="holdstart" value="<?php echo $holdStart; ?>">から
                                <input type="time" class="mx-2" name="holdend" value="<?php echo $holdEnd; ?>">まで
                            </td>
                        </tr>
                        <tr>
                            <th class="col-3 py-2 align-middle bg-lightbrown">
                                <div class="text-danger d-inline">*</div>場所
                            </th>
                            <td class="col-9 py-2 align-middle bg-white text-left">
                                <input type="submit" formaction="../getlatlng.php?type=event" value="位置情報の登録"><br>
                                <input type="text" name="lat" value="<?php echo $lat; ?>" class="d-transparent">
                                <input type="text" name="lng"value="<?php echo $lng; ?>" class="d-transparent d-inline">
                                <div class="text-start"><small>
                                    緯度：<?php echo $lat; ?><br>
                                    経度：<?php echo $lng; ?>
                                </small></div>
                            </td>
                        </tr>
                        <tr>
                            <th class="col-4 py-5 align-middle bg-lightbrown">
                                写真
                            </th>
                            <td class="col-8 py-1 align-middle bg-white w-100 h-100">
                                <label for="input1" class="box px-2">
                                <div class="border rounded border-2 border-navy px-2"><small>+写真を選択</small></div>
                                    <input type="file" id="input1" name="photo" class="pt-2" style="display: none; height: auto;">
                                </label><br>
                                <img id="sample1" class="w-75 h-75 py-2">
                            </td>
                        </tr>
                        <tr>
                            <th class="col-4 py-2 align-middle bg-lightbrown">
                                <div class="text-danger d-inline">*</div>ジャンル
                            </th>
                            <td class="col-8 py-2 align-middle bg-white text-left">
                                <select name="genre" class="d-inline mb-2" id ="select1">
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
                                <small>セレクトボックス内にない場合は<br>その他を選択しテキストボックス<br>に追加したいジャンルを入力してください</small>
                            </td>
                        </tr>
                        <tr>
                            <th class="col-4 py-2 align-middle bg-lightbrown">
                                <div class="text-danger d-inline">*</div>特徴
                            </th>
                            <td class="col-8 py-2 align-middle bg-white">
                                <textarea name="feature" value="<?php echo $feature; ?>" class="w-75" rows="5" placeholder="イベントの開催場所、詳しい日時を含め、催しの特徴を入力してください。※200文字以内" maxlength="200"></textarea>
                            </td>
                        </tr>
                        <tr>
                            <th class="col-4 py-2 align-middle bg-lightbrown">
                                リンク
                            </th>
                            <td class="col-8 py-2 align-middle bg-white">
                                <input type="text" name="link" value="<?php echo $link; ?>" class="w-75" placeholder="SNSやHPのURLを貼り付け" />
                            </td>
                        </tr>
                </table>
                <input class="text-center mb-3 px-3 py-2" type="submit" formaction="" value="投稿する">
            </form>
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

</html>