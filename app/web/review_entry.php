<?php
    $userId = '';
    $shopId = '';
    $shopName = '';
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

    //デフォルト設定
    $score = '3';
    $ambi = '';
    $crowd = '3';

    if (checkExistsReview($userId, $shopId, $num) != PDO::PARAM_NULL) {
        $reviewData = separateReviewData($userId, $shopId);
        error_log('score:'.$score);
        error_log('ambi:'.$ambi);
        error_log('crowd:'.$crowd);
        $score = $reviewData[0];
        $ambi = $reviewData[1];
        $crowd = $reviewData[2];
    }

    $scoreStr = <<<EOD
    <div class="review">
    <small>星の数を選択してください。</small>
    <div class="stars">
    <span>
    EOD;
    for ($i = 0; $i < 5; $i++) {
        $additions = '';
        if (($i+1) == 1) {
            $additions .= ' required';
        }
        if (($i+1) == $score) {
            $additions .= ' checked="checked"';
        }
        $scoreStr .= '<input id="review0'.($i+1).'" type="radio" name="score" value="'.(5-$i).'"'.$additions.'><label for="review0'.($i+1).'">★</label>';
    }
    $scoreStr .= <<<EOD
    </span>
    </div>
    </div>
    EOD;

    $ambiStr = <<<EOD
    <select name="ambi">
    <option hidden>選択してください</option>
    EOD;
    $ambiStr .= <<<EOD
    <option value="">特になし</option>
    </select>
    EOD;

    $crowdStr = <<<EOD
    空 <input name="crowd" type="range" list="my-datalist" min="1" max="5"> 混　
    <datalist id="my-datalist">
    EOD;
    $crowdStr .= <<<EOD
    </datalist>
    EOD;
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
        <form method="post" action="review_entry_confirm.php">
            <input type="hidden" name="userid" value="<?php echo $userId; ?>">
            <input type="hidden" name="shopid" value="<?php echo $shopId; ?>">
            <input type="hidden" name="shopname" value="<?php echo $shopName; ?>">
            <table class="table border-top border-navy align-middle">
                    <tr>
                        <th class="col-5 py-4 bg-lightbrown">
                            <div class="text-danger d-inline">*</div>味
                        </th>
                        <td class="col-7 py-4 bg-white w-80">
                            <?php echo $scoreStr; ?>
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