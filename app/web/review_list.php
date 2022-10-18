<?php
require_once '../DBConnection.php';
require_once '../database_function/review_sql.php';
require_once '../database_function/users_sql.php';
require_once 'list.php';

define('TABLE_NAME_REVIEWS', 'reviews');
define('TABLE_NAME_USERS', 'users');
?>

<?php
    $shopId = $_GET["shopid"];
    $reviewData = getReviewData($shopId);
    $allUserId = getAllUserIdByReviews($shopId);
    $userIdArray = array();
    for ($i = 0; $i < count($allUserId); $i++) {
        array_push($userIdArray, $allUserId[$i]['id']);
    }
    $uniqueUserId = array_unique($userIdArray);

    $shopAmbiString = '';
    
    $avarageScore = 0.0;
    //レビューが登録されているか確認
    if ($reviewData != PDO::PARAM_NULL) {
        $scoreArray = array(); //評価点
        $ambiArray = array(); //雰囲気
        $crowdArray = array(); //混み具合
        //レビュー
        $timeArray = array();
        $restTimeArray = array();
        foreach ($reviewData as $review) {
            if ($review["review_num"] == 100) {
                array_push($scoreArray, $review["review"]);
            } else if ($review["review_num"] == 200) {
                array_push($ambiArray, $review["review"]);
            } else if ($review["review_num"] == 300) {
                array_push($crowdArray, $review["review"]);
                array_push($timeArray, $review["time"]);
            }
        }
        foreach ($uniqueUserId as $userId) {
            $restTime = getRestTimeByUserId($userId);
            array_push($restTimeArray, $restTime['rest_start'].'~'.$restTime['rest_end']);
        }
        $totalScore = 0;
        for ($i = 0; $i < count($scoreArray); $i++) {
            $totalScore += intval($scoreArray[$i]);
        }
        $avarageScore = floatval($totalScore/count($scoreArray));

        //レビューから総合の店の雰囲気を取り出す
        $matchAmbi = return_max_count_item($ambiArray);
        foreach ($matchAmbi as $ambi) {
            if ($ambi === end($matchAmbi)) {
                $shopAmbiString .= AMBIENCE_LIST[$ambi];
            } else {
                $shopAmbiString .= AMBIENCE_LIST[$ambi].', ';
            }
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
    <title>ひるまちGO|みんなのレビュー</title>
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
    <header class="mt-5 col-12">
        <div class="container px-3 pt-5 bg-imagecolor">
            <h3 class="text-light">みんなのレビュー</h3>
        </div>
    </header>

    <!-- CONTENTS -->
    <div class="container dx-2 my-5 bg-lightnavy">
        <div class="bg-navy text-light mb-3">
            <div class="px-2 pt-3 col-12 border-bottom-3">
                <h3 class="h3"><?php echo $_GET["shopname"] ?></h3>
            </div>
            <div class="px-2">
                <?php if (gettype($avarageScore) == 'double') { ?>
                    <div class="fw-bold pt-2 pb-0">平均の評価： <?php printf("%.1f", $avarageScore); ?>点</div>
                    <div class="fw-bold pt-0 pb-1">店の雰囲気： <?php echo $shopAmbiString ?></div>
                <?php } else { ?>
                    <p class="fw-normal"><?php echo $avarageScore ?></p>
                <?php } ?> 
            </div>
        </div>

        <!-- <div>
            <p>あなたの昼休み時間では…<?php //ユーザの昼休み時間の混み具合をもとめて表示する ?></p>
        </div> -->

        <div class="bg-white">
            <?php if (gettype($avarageScore) == 'double') {
                for ($i = 0; $i < count($scoreArray); $i++) { ?>
                <table class="table border-navy px-3 bg-navy">
                    <?php $time = explode(' ', $timeArray[$i])[0] ?>
                    <thead><?php echo "レビュー日：".$time ?><div class="text-right"><?php echo "昼やすみ　：".$restTimeArray[$i] ?></div></thead>
                    <tr>
                        <th class="col-5 py-3 bg-lightorange text-dark">
                            評価
                        </th>
                        <td class="col-7 py-3 bg-white">
                            <?php echo $scoreArray[$i] . '点'; ?>
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
                <hr>
            <?php } 
                }?>
        </div>
    </div>

        <!-- Footer-->
        <footer class="bg-black text-center py-2 fixed-bottom">
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
    
    <!-- Feedback Modal-->
    <div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary-to-secondary p-4">
                    <h5 class="modal-title font-alt text-white" id="feedbackModalLabel">Send feedback</h5>
                    <button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body border-0 p-4"> -->
                    <!-- * * * * * * * * * * * * * * *-->
                    <!-- * * SB Forms Contact Form * *-->
                    <!-- * * * * * * * * * * * * * * *-->
                    <!-- This form is pre-integrated with SB Forms.-->
                    <!-- To make this form functional, sign up at-->
                    <!-- https://startbootstrap.com/solution/contact-forms-->
                    <!-- to get an API token!-->
                    <form id="contactForm" data-sb-form-api-token="API_TOKEN">
                        <!-- Name input-->
                        <div class="form-floating mb-3">
                            <input class="form-control" id="name" type="text" placeholder="Enter your name..." data-sb-validations="required" />
                            <label for="name">Full name</label>
                            <div class="invalid-feedback" data-sb-feedback="name:required">A name is required.</div>
                        </div>
                        <!-- Email address input-->
                        <div class="form-floating mb-3">
                            <input class="form-control" id="email" type="email" placeholder="name@example.com" data-sb-validations="required,email" />
                            <label for="email">Email address</label>
                            <div class="invalid-feedback" data-sb-feedback="email:required">An email is required.</div>
                            <div class="invalid-feedback" data-sb-feedback="email:email">Email is not valid.</div>
                        </div>
                        <!-- Phone number input-->
                        <div class="form-floating mb-3">
                            <input class="form-control" id="phone" type="tel" placeholder="(123) 456-7890" data-sb-validations="required" />
                            <label for="phone">Phone number</label>
                            <div class="invalid-feedback" data-sb-feedback="phone:required">A phone number is required.</div>
                        </div>
                        <!-- Message input-->
                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="message" type="text" placeholder="Enter your message here..." style="height: 10rem" data-sb-validations="required"></textarea>
                            <label for="message">Message</label>
                            <div class="invalid-feedback" data-sb-feedback="message:required">A message is required.</div>
                        </div>
                        <!-- Submit success message-->
                        <!---->
                        <!-- This is what your users will see when the form-->
                        <!-- has successfully submitted-->
                        <div class="d-none" id="submitSuccessMessage">
                            <div class="text-center mb-3">
                                <div class="fw-bolder">Form submission successful!</div>
                                To activate this form, sign up at
                                <br />
                                <a href="https://startbootstrap.com/solution/contact-forms">https://startbootstrap.com/solution/contact-forms</a>
                            </div>
                        </div>
                        <!-- Submit error message-->
                        <!---->
                        <!-- This is what your users will see when there is-->
                        <!-- an error submitting the form-->
                        <div class="d-none" id="submitErrorMessage">
                            <div class="text-center text-danger mb-3">Error sending message!</div>
                        </div>
                        <!-- Submit Button-->
                        <div class="d-grid"><button class="btn btn-primary rounded-pill btn-lg disabled" id="submitButton" type="submit">Submit</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
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
function return_max_count_item($list,&$count = null){
    if(empty($list)){
        $count = 0;
        return null;
    }
 
    //値を集計して降順に並べる
    $list = array_count_values($list);
    arsort($list);
 
    //最初のキーを取り出す
    $before_key = '';
    $before_val = 0;
    $no1_list = array();
 
    //2番目以降の値との比較
    foreach ($list as $key => $val){
        if($before_val > $val){
            break;
        }else {
            // 個数が同値の場合は配列に追加する
            array_push($no1_list,$key);
            $before_key = $key;
            $before_val = $val;
        }
    }
    $count = $before_val;
    if(count($no1_list) > 1){
        //同値の場合の処理があればここに書く、今回はarray_shiftで最初に追加したkeyを返した
        return $no1_list;
    }else{
        return $before_key;
    }
}
?>