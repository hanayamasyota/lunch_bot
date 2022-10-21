<?php
require_once '../DBConnection.php';
require_once '../database_function/users_sql.php';
require_once 'list.php';

define('TABLE_NAME_USERS', 'users');

$userId = $_GET['userid'];

//デフォルトの値
$nickName = null;
$nowTime = date('D:i');
$restStart = $nowTime;
$restEnd = $nowTime;
$ambience = null;

$selectBox = <<<EOD
<select name="ambi">
<option hidden>選択してください</option>
EOD;

//更新の場合は前の値をフォームにセットしておく
$setting = getPersonalSetting($userId);
if (($setting['rest_start'] != null) && ($setting['rest_end'] != null) && ($setting['nickname'] != null)) {
    $nickName = $setting['nickname'];
    $restStart = $setting['rest_start'];
    $restEnd = $setting['rest_end'];
    $ambience = $setting['ambience'];

    $ambiNum = intval($setting['ambience']);
    error_log('ambinum:'.$ambiNum);

    for ($i = 1; $i <= count(AMBIENCE_LIST); $i++) {
        if ($i == $ambiNum) {
            $selectBox .= '<option value="'.$i.'" selected>'.AMBIENCE_LIST[$i].'</option>';
        } else {
            $selectBox .= '<option value="'.$i.'">'.AMBIENCE_LIST[$i].'</option>';
        }
    }
} else {
    for ($i = 1; $i <= count(AMBIENCE_LIST); $i++) {
        $selectBox .= '<option value="'.$i.'">'.AMBIENCE_LIST[$i].'</option>';
    }
}

$selectBox .= <<<EOD
<option value="">特になし</option>
</select>
EOD;


?>


<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>ひるまちGO|個人設定</title>
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
            <h1 class="pt-3 font-nicokaku pe-1" style="display: inline-block;">ひるまち</h1><h1 class="pt-3 font-rc" style="display: inline-block;">GO</h1>
        </div>
    </nav>
    <!-- Mashead header-->
    <header class="mt-5">
        <div class="container px-3 pt-5 bg-imagecolor">
            <p class="text-light h3">個人設定</p>
        </div>
    </header>

    <!-- CONTENTS -->
    <div class="container dx-3 my-5 bg-lightnavy">
        <div>
            <p class="fw-bold text-center">
                <div class="text-danger d-inline">*</div>必要事項を入力してください。
            </p>
        </div>
        <form method="post" action="setting_confirm.php">
            <input type="hidden" name="userid" value="<?php echo $userId; ?>">
            <table class="table border-top border-navy vertical-middle">
                    <tr>
                        <th class="col-5 py-4 bg-lightbrown">
                            <div class="text-danger d-inline">*</div>ニックネーム
                            <small><p class="text-end">(12文字以内)</p></small>
                        </th>
                        <td class="col-7 pt-2 pb-0 bg-white">
                            <input type="text" name="nickname" maxlength="12" value="<?php echo $nickName; ?>" required>
                            <small>
                                <p>※「みんなのレビュー」で表示されます</p>
                            </small>
                        </td>
                    </tr>
                    <tr>
                        <th class="col-5 py-4 bg-lightbrown">
                            <div class="text-danger d-inline">*</div>昼休み時間
                        </th>
                        <td class="col-7 py-4 bg-white">
                            <input type="time" name="start" min="09:00" max="15:00" value="<?php echo $restStart; ?>" required>~
                            <input type="time" name="end" min="09:00" max="15:00" value="<?php echo $restEnd; ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <th class="col-5 py-4 bg-lightbrown">
                            好みの雰囲気
                        </th>
                        <td class="col-7 py-4 bg-white text-left">
                            <?php echo $selectBox; ?>
                            <!-- <select name="ambi">
                                <option hidden>選択してください</option>
                                <option value="1">おしゃれ</option>
                                <option value="2">たのしい</option>
                                <option value="3">にぎやか</option>
                                <option value="4">落ち着いている</option>
                                <option value="5">個性的</option>
                                <option value="6">高級志向</option>
                                <option value="7">テーマ性がある</option>
                                <option value="">特になし</option>
                            </select> -->
                        </td>
                    </tr>
            </table>
            <div class="text-center">
                <button type="submit" class="btn-dark mb-3 text-center">
                    変更を適用
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


