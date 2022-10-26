<?php
$name = '';
$email = '';
$psword = '';

$status = 'first';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $psword = $_POST["password"];

    $status = checkExistsEmail($email);
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>ひるまちGO|オーナー用ログイン画面</title>
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
    <script src="https://code.jquery.com/jquery-3.6.1.js" integrity="sha256-3zlB5s2uwoUzrXK3BT7AX3FyvojsraNFxCc2vC/7pNI=" crossorigin="anonymous"></script>
</head>

<style>
    @import url(https://fonts.googleapis.com/css?family=Open+Sans:400);

    @import url(http://weloveiconfonts.com/api/?family=fontawesome);

[class*="fontawesome-"]:before {
  font-family: 'FontAwesome', sans-serif;
}

.form-wrapper {
  background: #fafafa;
  margin: 3em auto;
  padding: 0 1em;
  max-width: 370px;
}

.signup-h1 {
  text-align: center;
  padding: 2em 0;
}

.form-item {
  margin-bottom: 0.75em;
  width: 100%;
}

.signup-input {
  background: #fafafa;
  border: none;
  border-bottom: 2px solid #e9e9e9;
  color: #666;
  font-family: 'Open Sans', sans-serif;
  font-size: 1em;
  height: 50px;
  transition: border-color 0.3s;
  width: 100%;
}

.signup-input:focus {
  border-bottom: 2px solid #c0c0c0;
  outline: none;
}

.button-panel {
  margin: 2em 0 0;
  width: 100%;
}

.button-panel .button {
  background: #f16272;
  border: none;
  color: #fff;
  cursor: pointer;
  height: 50px;
  font-family: 'Open Sans', sans-serif;
  font-size: 1.2em;
  letter-spacing: 0.05em;
  text-align: center;
  text-transform: uppercase;
  transition: background 0.3s ease-in-out;
  width: 100%;
}

.button:hover {
  background: #ee3e52;
}

.form-footer {
  font-size: 1em;
  padding: 2em 0;
  text-align: center;
}

.form-footer a {
  color: #8c8c8c;
  text-decoration: none;
  transition: border-color 0.3s;
}

.form-footer a:hover {
  border-bottom: 1px dotted #8c8c8c;
}

</style>

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
            <p class="text-light h3">サインアップ</p>
        </div>
    </header>



    <!-- Contents-->
    <?php
    if ($status === 'first') {
    ?>
    <div class="form-wrapper">
        <div class="signup-h1">
            <h1>サインアップ</h1>
        </div>
        <form method="POST" action="">
            <div class="form-item">
                <label for="email"></label>
                <input class="signup-input" type="email" name="name" required="required"  placeholder="ニックネーム" />
            </div>
            <div class="form-item">
                <label for="email"></label>
                <input class="signup-input" type="email" name="email" required="required" placeholder="メールアドレス" />
            </div>
            <div class="form-item">
                <label for="password"></label>
                <input class="signup-input" type="password" name="password" required="required" placeholder="パスワード" />
            </div>
            <div class="form-item">
                <label for="password"></label>
                <input class="signup-input" type="password" name="confirm" oninput="CheckPassword(this)" required="required" placeholder="パスワード(確認用)" />
            </div>
            <div class="button-panel">
                <input type="submit" class="button" title="Log In" value="登録する" />
            </div>
        </form>
        <div class="form-footer">
            <p><a href="owner_login.php">すでにアカウントをお持ちの方はこちら</a></p>
        </div>
    </div>
    <?php
    } else if ($status === 'failed') {
    ?>
        <div class="form-wrapper">
            <div>そのメールアドレスはすでに登録されています。</div>
        <div class="signup-h1">
            <h1>サインアップ</h1>
        </div>
        <form method="POST" action="">
            <div class="form-item">
                <label for="email"></label>
                <input class="signup-input" type="email" name="name" required="required"  placeholder="ニックネーム" />
            </div>
            <div class="form-item">
                <label for="email"></label>
                <input class="signup-input" type="email" name="email" required="required" placeholder="メールアドレス" />
            </div>
            <div class="form-item">
                <label for="password"></label>
                <input class="signup-input" type="password" name="password" required="required" placeholder="パスワード" />
            </div>
            <div class="form-item">
                <label for="password"></label>
                <input class="signup-input" type="password" name="confirm" oninput="CheckPassword(this)" required="required" placeholder="パスワード(確認用)" />
            </div>
            <div class="button-panel">
                <input type="submit" class="button" title="Log In" value="登録する" />
            </div>
        </form>
        <div class="form-footer">
            <p><a href="owner_login.php">すでにアカウントをお持ちの方はこちら</a></p>
        </div>
    </div>
    <?php
    } else if ($status == 'success') {
    ?>
    <div class="">
        <p>登録が完了しました。引き続きログインをお願いします。</p>
        <a href="owner_login.php">ログインはこちら</a>
    </div>
    <?php } ?>

    <!-- Footer-->
    <footer class="bg-black text-center py-2 mt-5 fixed-bottom">
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

<script>
	function CheckPassword(confirm) {
		// 入力値取得
		var input1 = password.value;
		var input2 = confirm.value;
		// パスワード比較
		if(input1 != input2){
			confirm.setCustomValidity("入力値が一致しません。");
		}else{
			confirm.setCustomValidity('');
		}
	}
</script>