<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>ひるまちGO|固定店舗登録</title>
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
            <p class="text-light h3">宣伝したいことを登録</p>
        </div>
    </header>



    <!-- Contents-->
    <div class="container dx-2 my-5 bg-lightnavy text-center">
        <form method="post" action="#">
        <table class="table border-top border-navy align-middle mb-4 text-nowrap">
            <thead class="border border-start">フォームの入力をしてください。</thead>
            <tr>
                <th class="col-3 py-2 align-middle bg-lightbrown">
                    <div class="text-danger d-inline">*</div>店名
                </th>
                <td class="col-9 py-2 align-middle bg-white">
                    <input type="text" name="shopname" placeholder="飲食店の名前を入力" required>
                </td>
            </tr>

            
            <tr>
                <th class="col-3 py-2 align-middle bg-lightbrown">
                    <div class="text-danger d-inline">*</div>開店日
                </th>
                <td class="col-9 py-2 font-weight-normal align-middle bg-white">
                    <input type="date" name="holddate" class="w-35" required>から
                </td>
            </tr>
            <tr>
                <th class="col-3 py-2 align-middle bg-lightbrown">
                    <div class="text-danger d-inline">*</div>営業時間
                </th>
                <td class="col-9 py-2 align-middle bg-white">
                    <input type="time" name="holdstart" required>から
                    <input type="time" name="holdend" required>まで
                </td>
            </tr>

            <tr>
                <th class="col-3 py-5 align-middle bg-lightbrown">
                    写真
                </th>
                <td class="col-9 py-1 align-middle bg-white">
                    <label for="input1" class="box px-2">
                        <small>+写真を選択</small>
                        <input type="file" id="input1" name="photo" class="pt-2" style="display: none;" />
                    </label><br>
                    <img id="sample1" class="w-75 h-75 py-2">
                </td>
            </tr>

            <tr>
                <th class="col-3 py-2 align-middle bg-lightbrown">
                    <div class="text-danger d-inline">*</div>ジャンル
                </th>
                <td class="col-9 py-2 align-middle bg-white">
                    <select name="genre" class="d-inline" requierd>
                        <option hidden value="">選択してください</option>
                        <option value="1">食事</option>
                        <option value="2">学び</option>
                        <option value="3">工作・体験</option>
                        <option value="4">フリーマーケット</option>
                        <option value="5">芸術・音楽</option>
                        <option value="999">その他</option>
                    </select>
                    <input type="text" class="w-25 d-inline"><br>
                    <small>セレクトボックス内にない場合は<br>その他を選択し右欄に入力してください</small>
                </td>
            </tr>

            <tr>
                <th class="col-3 py-2 align-middle bg-lightbrown">
                    <div class="text-danger d-inline">*</div>特徴
                </th>
                <td class="col-9 py-2 align-middle bg-white">
                    <textarea name="feature" class="w-75" rows="5" placeholder="住所や定休日などの情報を含め、お店の特徴を入力してください。" required></textarea>
                </td>
            </tr>

            <tr>
                <th class="col-3 py-2 align-middle bg-lightbrown">
                    リンク
                </th>
                <td class="col-9 py-2 align-middle bg-white">
                    <input type="text" name="link" class="w-75" placeholder="SNSやHPのURLを貼り付け"/>
                </td>
            </tr>
        </table>
        <input type="submit" value="投稿する">
        </form>

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

<script>
        $("#input1").on("change", function (e) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $("#sample1").attr("src", e.target.result);
            }
            reader.readAsDataURL(e.target.files[0]);
        });

        function Switch() {
            const radio = document.getElementsByName('radio1');
            const shop = document.getElementsByClassName('shopList');
            const event = document.getElementsByClassName('eventList');
            if (radio[0].checked) {
                shop[0].style.display = '';
                shop[1].style.display = '';
                event[0].style.display = 'none';
                event[1].style.display = 'none';
                
            }
            else if (radio[1].checked) {
                shop[0].style.display = 'none';
                shop[1].style.display = 'none';
                event[0].style.display = '';
                event[1].style.display = '';
            }
            else {
                shop[0].style.display = 'none';
                shop[1].style.display = 'none';
                event[0].style.display = 'none';
                event[1].style.display = 'none';
            }
        }

        window.onload = Switch();


        


        // $('#myImage').on('change', function (e) {
        //     var reader = new FileReader();
        //     reader.onload = function (e) {
        //         $("#preview").attr('src', e.target.result);
        //     }
        //     reader.readAsDataURL(e.target.files[0]);
        // });

</script>




</html>