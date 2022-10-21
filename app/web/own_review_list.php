<?php
require_once '../DBConnection.php';
require_once '../database_function/review_sql.php';
require_once '../database_function/users_sql.php';
require_once 'list.php';

define('TABLE_NAME_REVIEWS', 'reviews');
define('TABLE_NAME_USERS', 'users');
?>

<?php
$userId = $_GET['userid'];
$ownReviewData = getDataByReviews($userId);

if ($ownReviewData != PDO::PARAM_NULL) {
    //レビュー
    $scoreArray = array(); //評価点
    $ambiArray = array(); //雰囲気
    $crowdArray = array(); //混み具合

    $timeArray = array();
    $shopNameArray = array();
    $shopIdArray = array();
    foreach ($ownReviewData as $review) {
        if ($review["review_num"] == 100) {
            array_push($scoreArray, $review["review"]);
        } else if ($review["review_num"] == 200) {
            array_push($ambiArray, $review["review"]);
        } else if ($review["review_num"] == 300) {
            array_push($crowdArray, $review["review"]);
            array_push($timeArray, $review["time"]);
            array_push($shopNameArray, $review['shopname']);
        }
    }

    foreach ($shopNameArray as $shopName) {
        $shopId = getShopIdByReviews($userId, $shopName);
        array_push($shopIdArray, $shopId);
    }

//レビューが登録されていない場合
} else {
    $avarageScore = 'まだレビューが登録されていません。';
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>ひるまちGO|あなたのレビュー一覧</title>
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
    <script src="js/confirm.js"></script>
</head>

<body id="page-top" class="bg-base">
    <!-- Navigation-->
    <nav class="fixed-top shadow-sm" id="mainNav">
        <div class="container px-5">
            <h1 class="d-inline pt-3 font-nicokaku pe-1">ひるまち</h1><h1 class="d-inline pt-3 font-rc">GO</h1>
        </div>
    </nav>
    <!-- Mashead header-->
    <header class="mt-5 col-12">
        <div class="container px-3 pt-5 bg-imagecolor">
            <h3 class="text-light">あなたのレビュー一覧</h3>
        </div>
    </header>

    <!-- CONTENTS -->
    <div class="container dx-2 my-5 bg-lightnavy">
        <div class="bg-white">
            <?php for ($i = 0; $i < count($scoreArray); $i++) { ?>
                <h5 class="bg-navy text-light mb-0 py-2 align-middle">
                    <?php echo $shopNameArray[$i]; ?>
                </h5>
                <table class="table border-navy px-3 mb-0">
                    <?php $time = explode(' ', $timeArray[$i])[0] ?>
                    <thead><?php echo "レビュー日：".$time ?></thead>
                    <tr>
                        <th class="col-5 py-3 bg-lightorange text-dark">
                            評価
                        </th>
                        <td class="col-7 py-3 bg-white">
                            <?php
                            $scorePreview = '';
                            for ($n = 0; $n <= 0; $n++) {
                                if ($n < intval($scoreArray[$i]) {
                                    $scorePreview .= '<div class="d-inline preview-star">★</div>';
                                } else {
                                    $scorePreview .= '<div class="d-inline preview-star-gray">★</div>';
                                }
                            }
                            echo $scorePreview;
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="col-5 py-3 bg-lightorange text-dark">
                            雰囲気
                        </th>
                        <td class="col-7 py-3 bg-white">
                            <?php echo AMBIENCE_LIST[$ambiArray[$i]]; ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="col-5 py-3 bg-lightorange text-dark">
                            混み具合
                        </th>
                        <td class="col-7 py-3 bg-white">
                            <?php echo CROWD_LIST[$crowdArray[$i]]; ?>
                        </td>
                    </tr>
                </table>
                <div class="text-end pt-2 px-2 h-2rem">
                    <form method="POST" action="review_entry.php" class="d-inline pe-2">
                        <input type="hidden" value="<?php echo $userId; ?>" name="userid">
                        <input type="hidden" value="<?php echo $shopIdArray[$i]; ?>" name="shopid">
                        <input type="hidden" value="<?php echo $shopNameArray[$i]; ?>" name="shopname">
                        <button type="submit" class="btn-primary w-25 h-2rem rounded">編集</button>
                    </form>
                    <form method="POST" action="review_delete.php" class="d-inline ps-2">
                        <input type="hidden" value="<?php echo $userId; ?>" name="userid">
                        <input type="hidden" value="<?php echo $shopIdArray[$i]; ?>" name="shopid">
                        <input type="hidden" value="<?php echo $shopNameArray[$i]; ?>" name="shopname">
                        <button type="submit" class="btn-danger w-25 h-2rem rounded">削除</button>
                    </form>
                </div>
                <hr>
            <?php } ?>
        </div>
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