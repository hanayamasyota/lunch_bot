<?php
    $status = '';
    if($_SERVER["REQUEST_METHOD"] != "POST") {
        $shopId = $_GET["shopid"];
        $shopName = $_GET["shopname"];
        $userId = $_GET["userid"];
        $status = '登録';
    } else {
        $shopId = $_POST["shopid"];
        $shopName = $_POST["shopname"];
        $userId = $_POST["userid"];
        $status = '編集';
    }

    $message = "";
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
        <p><?php echo $message; ?></p>
        <form method="post" action="review_entry_confirm.php">
            <input type="hidden" name="userid" value="<?php echo $userId; ?>">
            <input type="hidden" name="shopid" value="<?php echo $shopId; ?>">
            <input type="hidden" name="shopname" value="<?php echo $shopName; ?>">
            <table class="table border-top border-navy vertical-middle">
                    <tr>
                        <th class="col-5 py-4 bg-lightbrown">
                            <div class="text-danger d-inline">*</div>味
                        </th>
                        <td class="col-7 py-4 bg-white w-80">
                            <div class="review">
			                    <small>星の数を選択してください。</small>
	    		                <div class="stars">
		    		                <span>
		      			                <input id="review01" type="radio" name="score" value="5" required><label for="review01">★</label>
		      			                <input id="review02" type="radio" name="score" value="4"><label for="review02">★</label>
		      			                <input id="review03" type="radio" name="score" value="3" checked="checked"><label for="review03">★</label>
		      			                <input id="review04" type="radio" name="score" value="2"><label for="review04">★</label>
		      			                <input id="review05" type="radio" name="score" value="1"><label for="review05">★</label>
		    		                </span>
	  			                </div>
			                </div>
                        </td>
                    </tr>
                    <tr>
                        <th class="col-5 py-4 bg-lightbrown">
                            <div class="text-danger d-inline">*</div>雰囲気
                        </th>
                        <td class="col-7 py-4 bg-white text-left w-80">
                            <select name="ambi" required>
				                <option value="" hidden>選択してください</option>
				                <option value="1">おしゃれ</option>
				                <option value="2">たのしい</option>
				                <option value="3">にぎやか</option>
				                <option value="4">おちつきがある</option>
				                <option value="5">個性的</option>
				                <option value="6">高級志向</option>
				                <option value="7">テーマ性がある</option>
			                </select>
                        </td>
                    </tr>
                    <tr>
                        <th class="col-5 py-4 bg-lightbrown">
                            <div class="text-danger d-inline">*</div>混み具合
                        </th>
                        <td class="col-7 py-4 bg-white w-80">
			                空 <input name="crowd" type="range" list="my-datalist" min="1" max="5"> 混　
			                <datalist id="my-datalist">
  				                <option value="1">
  				                <option value="2">
  				                <option value="3">
  				                <option value="4">
  				                <option value="5">
			                </datalist>
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