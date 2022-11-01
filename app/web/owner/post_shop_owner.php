<?php
session_start();

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
    <link href="../css/form.css" rel="stylesheet" />
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
        <form method="post" action="post_shop_owner_confirm.php" enctype="multipart/form-data">
    
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
                    <input type="time" name="holdstart" required><div class="px-1">から</div>
                    <input type="time" name="holdend" required><div class="px-1">まで</div>
                </td>
            </tr>

            <tr>
                <th class="col-3 py-2 align-middle bg-lightbrown">
                    <div class="text-danger d-inline">*</div>場所
                </th>
                <td class="col-9 py-2 align-middle bg-white">
                    <!-- readonlyにする -->
                    <a href="../getlatlng.php">こちらのリンクから設定してください</a>
                    <input type="text" name="lat" value="<?php echo $lat; ?>" class="" required>
                    <input type="text" name="lng"value="<?php echo $lng; ?>" class="d-inline" required>
                </td>
            </tr>

            <tr>
                <th class="col-3 py-5 align-middle bg-lightbrown">
                    写真
                </th>
                <td class="col-9 py-1 align-middle bg-white">
                    <label for="input1" class="box px-2">
                        <small>+写真を選択</small>
                        <input type="file" id="input1" name="photo" class="pt-2" style="display: none;">
                    </label><br>
                    <img id="sample1" class="w-75 h-75 py-2">
                </td>
            </tr>

            <tr>
                <th class="col-3 py-2 align-middle bg-lightbrown">
                    <div class="text-danger d-inline">*</div>ジャンル
                </th>
                <td class="col-9 py-2 align-middle bg-white">
                    <select name="genre" class="d-inline" requierd id ="select1">
                        <option hidden value="">選択してください</option>
                        <option value="1">食事</option>
                        <option value="2">学び</option>
                        <option value="3">工作・体験</option>
                        <option value="4">フリーマーケット</option>
                        <option value="5">芸術・音楽</option>
                        <option value="999">その他</option>
                    </select>
                    <input type="text" class="w-25 d-inline" id="newgenre"><br>
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

        const myGenre = document.getElementById("select1");
        myGenre.onchange = changeGenre(myGenre);

        function changeGenre(genre) {
            let num = genre.selectedIndex;
            let str = genre.options[num].value;

            if (str == '999') {

            }
        }

        function disableNewGenre() {

        }

</script>




</html>