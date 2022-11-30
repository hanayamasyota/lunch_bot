<?php 
require_once 'score.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <style>
        .soto > h1,
        .uti {
            margin: .5rem;
            padding: .3rem;
            font-size: 1.2rem;
        }

        .uti {
            background: right/contain content-box border-box no-repeat
            url('/media/examples/rain.svg') white;
        }

        .uti > h2,
        .uti > p {
            margin: .2rem;
            font-size: 1rem;
        }

        .scroll {
            height: 200px;
            overflow-y: scroll;
        }
    </style>
</head>
<body>
    <div class="scroll">
    <?php if (!($legends == PDO::PARAM_NULL)) { //取得ログを表示する ?>
        <?php foreach ($legends as $legend) { ?>
        <article class="uti">
            <?php $name = getLegends($legend['legend_id']); ?>
            <?php $date = explode(' ', $legend['got_time'])[0]; ?>
            <p><?php echo $date; ?> 称号:「<?php echo $name; ?>」を獲得しました。</p>
        </article>
        <?php } ?>
    <?php }?>
    </div>
</body>
</html>