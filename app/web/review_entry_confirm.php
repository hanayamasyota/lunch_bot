<?php
require_once '../DBConnection.php';
require_once '../database_function/review_sql.php'; 
require_once '../database_function/users_sql.php';

define('TABLE_NAME_REVIEWS', 'reviews');
define('TABLE_NAME_USERS', 'users');
?>

<?php
    $num = 100;

    $postData = array();
    array_push($postData, $_POST['score']);
    array_push($postData, $_POST['ambi']);
    $time = explode(':', $_POST['visit_time']);
    $timeStr = $time[0].'時'.$time[1].'分';
    array_push($postData, $timeStr);
    array_push($postData, $_POST['crowd']);
    array_push($postData, $_POST['free']);

    $userId = $_POST['userid'];
    $shopId = $_POST['shopid'];
    $shopName = $_POST['shopname'];

    $message = '';
    if (checkExistsReview($userId, $shopId, $num) != PDO::PARAM_NULL) {
        $message = 'レビュー更新';
    } else {
        $message = 'レビュー登録';
    }

    $num = 1;
    foreach($postData as $data) {
        $nowTime = time()+32400;
        $nowTimeString = date('Y-m-d H:i:s', $nowTime);
        //同じ店をレビューしていないか確認
        if (checkExistsReview($userId, $shopId, $num) != PDO::PARAM_NULL) {
            updateReview($userId, $shopId, $num, $data, $nowTimeString, $shopName);
        } else {
            //レビューを登録する
            registerReview($userId, $shopId, $num, $data, $nowTimeString, $shopName);
        }
        $num += 1;
    }
    updateUser($userId, null);
?>


<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>ひるまちGO|<?php echo $message; ?>完了</title>
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
            <p class="text-light h3"><?php echo $message; ?>完了</p>
        </div>
    </header>

    <!-- CONTENTS -->
    <div class="container dx-3 my-5 bg-lightnavy">
        <p><?php echo $message.'が完了しました。'; ?></p>
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