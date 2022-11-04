<?php
$shopname = '';
$openDate = '';
$openTime = '';
$closeTime = '';
$lat = 0.0;
$lng = 0.0;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lat = floatval($_POST['lat']);
    $lng = floatval($_POST['lng']);
} else {
    $lat = null;
    $lng = null;
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>ひるまちGO|昼休みの過ごし方登録</title>
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
    <link href="css/form.css" rel="stylesheet" />
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
            <p class="text-light h3">昼休みの過ごし方登録</p>
        </div>
    </header>



    <!-- Contents-->
    <div class="container dx-1 my-5 bg-lightnavy text-center">
        <form method="post" action="post_shop_event_confirm.php" enctype="multipart/form-data">
        <input type="hidden" value="<?php echo $_GET["userid"]; ?>" name="userid">
        <table class="table border-top border-navy align-middle text-center" style="table-layout: fixed;">
            <thead class="border border-start">フォームの入力をしてください。</th>
            <tr>
                <th class="col-3 py-4 align-middle bg-lightbrown">
                    <div class="text-danger d-inline">*</div>名前
                </th>
                <td class="col-9 py-4 align-middle bg-white">
                    <input type="text" required>
                </td>
            </tr>

    <form>
            <tr>
                <th class="col-3 py-4 align-middle bg-lightbrown">
                    <div class="text-danger d-inline">*</div>種類
                </th>
        
                <td class="col-9 py-4 align-middle bg-white" required>
                        <input class="form-check-input m2-2 text-left" type="radio" id="x" name="radio1" value="shop" onclick="Switch()" checked="checked">
                        <label for="x" class="form-check-label">固定店舗</label><br>
                        <input class="form-check-input ms-2 text-left" type="radio" id="y" name="radio1" value="event" onclick="Switch()">
                        <label for="y" class="form-check-label">イベント・移動店舗</label><br>
                        <input class="form-check-input ms-2 text-left" type="radio" id="z" name="radio1" value="life" onclick="Switch()">
                        <label for="z" class="form-check-label">過ごし方</label>
                </td>
            </tr>

            <tr class="shopList">
                <th class="col-3 py-4 align-middle bg-lightbrown">
                    開店日
                </th>
                <td class="col-9 py-4 align-middle bg-white">
                    <input type="date" name="opendate">開店
                </td>
            </tr>
            <tr class="shopList">
                <th class="col-3 py-4 align-middle bg-lightbrown">
                    営業時間
                </th>
                <td class="col-9 py-4 align-middle bg-white">
                    <input type="time" name="opentime">から
                    <input type="time" name="closetime">まで<br>
                    <small class="text-left">※定休日等については下の「特徴」欄に入力してください</small>
                </td>
            </tr>
            
            <tr class="eventList">
                <th class="col-3 py-4 align-middle bg-lightbrown">
                    開催日
                </th>
                <td class="col-9 py-4 font-weight-normal align-middle bg-white">
                    <input type="date" name="holddatestart" class="w-35">から<br>
                    <input type="date" name="holddateend" class="w-35">まで<br>
                    ※1日だけの場合は同じ日にちを入力
                </td>
            </tr>
            <tr class="eventList">
                <th class="col-3 py-4 align-middle bg-lightbrown">
                    開催時間
                </th>
                <td class="col-9 py-4 align-middle bg-white">
                    <input type="time" name="holdstart">から
                    <input type="time" name="holdend">まで開催
                </td>
            </tr>

            <tr class="lifeList">
                <th class="col-3 py-4 align-middle bg-lightbrown">
                    <small>そこにいた<br>時間</small>
                </th>
                <td class="col-9 py-4 align-middle bg-white">
                    <input type="time" name="spendstart">から
                    <input type="time" name="spendend">まで
                </td>
            </tr>
    </form>
            <tr>
                <th class="col-3 py-2 align-middle bg-lightbrown">
                    <div class="text-danger d-inline">*</div>場所
                </th>
                <td class="col-9 py-2 align-middle bg-white">
                    <a href="getlatlng.php?type=user">こちらのリンクから設定してください</a><br>
                    <input type="text" name="lat" value="<?php echo $lat; ?>" class="d-transparent" required>
                    <input type="text" name="lng"value="<?php echo $lng; ?>" class="d-transparent d-inline" required>
                </td>
            </tr>

            <tr>
                <th class="col-3 py-5 align-middle bg-lightbrown">
                    写真
                </th>
                <td class="col-9 py-1 align-middle bg-white w-100 h-100">
                    <label for="input1" class="box px-2">
                        <small>+写真を選択</small>
                        <input type="file" id="input1" name="photo" class="pt-2" style="display: none;">
                    </label><br>
                    <img id="sample1" class="w-100 py-2" style="height: auto;">
                </td>
            </tr>

            <tr>
                <th class="col-3 py-4 align-middle bg-lightbrown">
                    <div class="text-danger d-inline">*</div>ジャンル
                </th>
                <td class="col-9 py-4 align-middle bg-white">
                    <select name="genre" class="d-inline" required id ="select1">
                        <option hidden value="">選択してください</option>
                        <option value="1">食事</option>
                        <option value="2">学び</option>
                        <option value="3">工作・体験</option>
                        <option value="4">フリーマーケット</option>
                        <option value="5">芸術・音楽</option>
                        <option value="999">その他</option>
                    </select>
                    <input type="text" class="w-25 d-inline" id="newgenre"><br>
                    <small class="text-left">セレクトボックス内にない場合は<br>その他を選択し右欄に入力してください</small>
                </td>
            </tr>

            <tr>
                <th class="col-3 py-4 align-middle bg-lightbrown">
                    特徴
                </th>
                <td class="col-9 py-4 align-middle bg-white">
                    <textarea name="feature" class="w-75" rows="4" maxlength="100" placeholder="それはどんなところですか？特徴を入力してください。※100文字以内"></textarea>
                </td>
            </tr>
        </table>
        <input class="text-center" type="submit" value="投稿する">
        </form>



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
            const life = document.getElementsByClassName('lifeList');
            if (radio[0].checked) {
                shop[0].style.display = '';
                shop[1].style.display = '';
                event[0].style.display = 'none';
                event[1].style.display = 'none';
                life[0].style.display = 'none';
                life[1].style.display = 'none';     
            }
            else if (radio[1].checked) {
                shop[0].style.display = 'none';
                shop[1].style.display = 'none';
                event[0].style.display = '';
                event[1].style.display = '';
                life[0].style.display = 'none';
                life[1].style.display = 'none';
            }
            else if (radio[2].checked) {
                shop[0].style.display = 'none';
                shop[1].style.display = 'none';
                event[0].style.display = 'none';
                event[1].style.display = 'none';
                life[0].style.display = '';
                life[1].style.display = '';
            }
            else {
                shop[0].style.display = 'none';
                shop[1].style.display = 'none';
                event[0].style.display = 'none';
                event[1].style.display = 'none';
                life[0].style.display = 'none';
                life[1].style.display = 'none';
            }
        }

        window.onload = Switch();

</script>




</html>