<?php
//1ページの表示件数
define('ONE_PAGE', 2);

require_once '../DBConnection.php';
require_once '../database_function/review_sql.php';
require_once '../database_function/users_sql.php';
require_once 'list.php';

define('TABLE_NAME_REVIEWS', 'reviews');
define('TABLE_NAME_USERS', 'users');
?>

<?php
$userId = "";
if($_SERVER["REQUEST_METHOD"] != "POST") {
        $userId = $_GET["userid"];
} else {
        $userId = $_POST["userid"];
}
$page = intval($_GET["now_page"]);
$ownReviewData = getPageReviewData($userId, $page);

$reviewCount = 0;

$scoreArray = array(); //評価点
$ambiArray = array(); //雰囲気
$crowdArray = array(); //混み具合

$timeArray = array();
$shopNameArray = array();
$shopIdArray = array();

$maxPage = 0;
$pageRange = 0;

if ($ownReviewData != PDO::PARAM_NULL) {
    //最大ページ数の計算
    $reviewCount = (getDataCountByReviews($userId) / 3);
    $maxPage = ceil($reviewCount / ONE_PAGE);
    error_log('max_page:'.$maxPage);
    //2がでたらok
    //レビュー
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
        $shopId = getShopIdByReviews($userId, $shopName)[0]['shopid'];
        array_push($shopIdArray, $shopId);
    }

    $pageRange = getPageRange($page, $maxPage);
    error_log('pagerange:'.$pageRange);

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
                <table class="table border-navy px-3 mb-0 align-middle">
                    <?php $time = explode(' ', $timeArray[$i])[0]; ?>
                    <thead><?php echo "レビュー日：".$time ?></thead>
                    <tr>
                        <th class="col-5 py-3 bg-lightorange text-dark">
                            評価
                        </th>
                        <td class="col-7 py-3 bg-white">
                            <?php
                            $scorePreview = '';
                            for ($n = 0; $n < 5; $n++) {
                                if ($n < intval($scoreArray[$i])) {
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
        
        <div class="pagination">
            <?php if ($page >= 2) { ?>
                <a href="javascript:form<?php echo ($page-1); ?>.submit();" class="page_feed">&laquo;</a>
                <?php echo createFormTemp(($page-1), $userId); ?>
            <?php } else { ?>
                <span class="first_last_page">&laquo;</span>
            <?php } ?>
            
            <?php for ($i = 1; $i <= $maxPage; $i++) { ?>
                <?php if (($i >= $page - $pageRange) && ($i <= $page + $pageRange)) { ?>
                    <?php if ($i == $page) { ?>
                        <span class="now_page_number"><?php echo $i; ?></span>
                    <?php } else { ?>
                        <a href="" onclick="javascript:form<?php echo $i; ?>.submit();" class="page_number"><?php echo $i; ?></a>
                        <?php echo createFormTemp($i, $userId); ?>
                    <?php } ?>
                <?php } ?>
            <?php } ?>

            <?php if($page < $maxPage) { ?>
                <a href="" onclick="document.form<?php echo ($page+1); ?>.submit();" class="page_feed">&raquo;</a>
                <?php echo createFormTemp(($page+1), $userId); ?>
            <?php } else { ?>
                <span class="first_last_page">&raquo;</span>
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

<?php
function createFormTemp($num, $userId) {
    $formTemp = '<form name="form'.$num.'" method="POST" action="own_review_list.php?now_page='.$num.'">';
    $formTemp .= '<input type="hidden" name="userid" value="'.$userId.'">';
    $formTemp .= '<input type="hidden" name="now_page" value="'.$num.'">';
    $formTemp .= '</form>';
    return $formTemp;
}

function getPageRange($page, $maxPage) {
    if($page == 1 || $page == $maxPage) {
        $range = 4;
    } elseif ($page == 2 || $page == $maxPage - 1) {
        $range = 3;
    } else {
        $range = 2;
    }
    return $range;
}
?>