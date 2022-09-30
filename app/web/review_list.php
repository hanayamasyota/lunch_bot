<?php
require_once '../DBConnection.php';
require_once '../database_function/review_sql.php';
?>

<?php
    $shopId = $_GET["shopid"];
    $reviewData = getReviewData($shopId);
    $avarageScore = 0.0;
    //レビューが登録されていない場合
    if ($reviewData != PDO::PARAM_NULL) {
        $reviewArray_1 = array();
        $reviewArray_2 = array();
        $reviewArray_3 = array();
        $reviewArray_3 = 
        foreach ($reviewData as $review) {
            if ($review["review_num"] == 100) {
                array_push($reviewArray_1, $review["review"]);
            } else if ($review["review_num"] == 200) {
                array_push($reviewArray_2, $review["review"]);
            } else if ($review["review_num"] == 300) {
                array_push($reviewArray_3, $review["review"]);
            }
        }
        $totalScore = 0;
        for ($i = 0; $i < count($reviewArray_1); $i++) {
            $totalScore += intval($reviewArray_1[$i]);
        }
        $avarageScore = floatval($totalScore/count($reviewArray_1));
    } else {
        $avarageScore = 'まだレビューが登録されていません。';
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>ひるまちぶらり|レビュー一覧</title>
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

<body id="page-top">
    <!-- Navigation-->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top shadow-sm" id="mainNav">
        <div class="container px-5">
            <p class="navbar-brand fw-bold">ひるまちぶらり</p>
        </div>
    </nav>
    <!-- Mashead header-->
    <header class="mt-6 mb-2 bg-lightbrown">
        <div class="container px-3 pt-5">
        </div>
    </header>

    <!-- CONTENTS -->
    <div class="container dx-3">
        <div class="px-2 mt-3 col-10 border-bottom-3">
            <h3 class="h3"><?php echo $_GET["shopname"] ?></h3>
        </div>
        <div class="px-2">
            <?php if (gettype($avarageScore) == 'double') { ?>
                <p class="fw-bold mt-2">平均の評価： <?php printf("%.1f", $avarageScore); ?>点</p>
                <hr>
            <?php } else { ?>
                <p class="fw-normal"><?php echo $avarageScore ?></p>
            <?php } ?> 
        </div>
            <?php if (gettype($avarageScore) == 'double') {
                for ($i = 0; $i < count($reviewArray_1); $i++) { ?>
                <table class="table table-bordered px-3">
                    <thead><?php echo  ?></thead>
                    <tr>
                        <th class="col-5 py-3">
                            レビュー項目１
                        </th>
                        <td class="col-7 py-3">
                            <?php echo $reviewArray_1[$i] . '点'; ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="col-5 py-3">
                            レビュー項目２
                        </th>
                        <td class="col-7 py-3">
                            <?php echo $reviewArray_2[$i]; ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="col-5 py-3">
                            レビュー項目３
                        </th>
                        <td class="col-7 py-3">
                            <?php echo $reviewArray_3[$i]; ?>
                        </td>
                    </tr>
                </table>
            <?php } 
                }?>
    </div>
    
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