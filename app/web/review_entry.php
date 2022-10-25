<?php
require_once '../DBConnection.php';
require_once '../database_function/review_sql.php';
require_once 'list.php';

define('TABLE_NAME_REVIEWS', 'reviews');
?>

<?php
    $status = '登録';
    $userId = '';
    $shopId = '';
    $shopName = '';
    if($_SERVER["REQUEST_METHOD"] != "POST") {
        $shopId = $_GET["shopid"];
        $shopName = $_GET["shopname"];
        $userId = $_GET["userid"];
    } else {
        $shopId = $_POST["shopid"];
        $shopName = $_POST["shopname"];
        $userId = $_POST["userid"];
    }

    //デフォルト設定
    $score = '';
    $ambi = '';
    $time = '';
    $crowd = '';
    $free = '';

    //すでに登録済みで編集をする場合は以前の値をもとに表示させる
    if (checkExistsReview($userId, $shopId, 1) != PDO::PARAM_NULL) {
        $reviewData = separateReviewData($userId, $shopId);
        $score = $reviewData[0]['review'];
        $ambi = $reviewData[1]['review'];
        $time = $reviewData[2]['review'];
        $crowd = $reviewData[3]['review'];
        $free = $reviewData[4]['review'];
        $status = '編集';
    } else {
        $score = '3';
        $nowTime = time()+32400;
        $crowd = '3';
    }

    $scoreStr = <<<EOD
    <div class="review">
    <small>星の数を選択してください。</small>
    <div class="stars">
    <span>
    EOD;
    for ($i = 1; $i <= 5; $i++) {
        $additions = '';
        if ($i == 1) {
            $additions .= ' required';
        }
        if (5-($i-1) == $score) {
            $additions .= ' checked="checked"';
        }
        $scoreStr .= '<input id="review0'.$i.'" type="radio" name="score" value="'.(5-($i-1)).'"'.$additions.'><label for="review0'.$i.'">★</label>';
    }
    $scoreStr .= <<<EOD
    </span>
    </div>
    </div>
    EOD;

    $ambiStr = <<<EOD
    <select name="ambi" required>
    <option hidden>選択してください</option>
    EOD;
    for ($i = 1; $i <= count(AMBIENCE_LIST); $i++) {
        $additions = '';
        if ($i == 1) {
            $additions .= ' required';
        }
        if ($i == $ambi) {
            $additions .= ' selected';
        }
        $ambiStr .= '<option value="'.$i.'"'.$additions.'>'.AMBIENCE_LIST[$i].'</option>';
    }
    $ambiStr .= <<<EOD
    </select>
    EOD;


    $timeStr = '<input type="time" name="visit_time" value="'.$time.'" class="py-2 px-4" required>';

    $crowdStr = '空 <input name="crowd" type="range" list="my-datalist" min="1" max="5" value="'.$crowd.'"> 混'.
    '<datalist id="my-datalist">';
    for ($i = 1; $i <= count(CROWD_LIST); $i++) {
        $additions = '';
        $crowdStr .= '<option value="'.$i.'">';
    }
    $crowdStr .= <<<EOD
    </datalist>
    EOD;

    $freeStr = '<textarea class="w-100 h-4rem placeholder="感想や備考等あれば記入してください(150字まで)" name="free" maxlength="150">'.$free.'</textarea>';
?>


<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>ひるまちGO|レビュー<?php echo $status; ?></title>
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
            <p class="text-light h3">レビュー<?php echo $status; ?></p>
        </div>
    </header>

    <!-- CONTENTS -->
    <div class="container dx-3 my-5 bg-lightnavy">
        <div class="bg-navy text-light">
            <div class="px-2 pt-3 col-12 border-bottom-3">
                <h3 class="h3"><?php echo $shopName ?>のレビュー</h3>
            </div>
        </div>
        <small><div class="text-danger d-inline">*</div>は必須項目です</small>
        <form method="post" action="review_entry_confirm.php">
            <input type="hidden" name="userid" value="<?php echo $userId; ?>">
            <input type="hidden" name="shopid" value="<?php echo $shopId; ?>">
            <input type="hidden" name="shopname" value="<?php echo $shopName; ?>">
            <table class="table border-top border-navy align-middle">
                    <tr>
                        <th class="col-5 py-4 bg-lightbrown">
                            <div class="text-danger d-inline">*</div>味
                        </th>
                        <td class="col-7 py-4 bg-white">
                            <?php echo $scoreStr; ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="col-5 py-4 bg-lightbrown">
                            <div class="text-danger d-inline">*</div>雰囲気
                        </th>
                        <td class="col-7 py-4 bg-white text-left">
                            <?php echo $ambiStr; ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="col-5 py-4 bg-lightbrown">
                            <div class="text-danger d-inline">*</div>来店時刻
                        </th>
                        <td class="col-7 py-4 bg-white">
                            <?php echo $timeStr; ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="col-5 py-4 bg-lightbrown">
                            <div class="text-danger d-inline">*</div>混み具合
                        </th>
                        <td class="col-7 py-4 bg-white">
                            <?php echo $crowdStr; ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="col-5 py-4 bg-lightbrown">
                            感想など
                        </th>
                        <td class="col-7 py-4 bg-white h-3rem">
                            <?php echo $freeStr; ?>
                        </td>
                    </tr>
            </table>
            <div class="text-center">
                <button type="submit" class="btn-dark mb-3 text-center">
                    レビューを登録する
                </button>
            </div>
        </form>
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