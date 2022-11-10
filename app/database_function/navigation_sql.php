<?php

//navigationテーブルへのデータ挿入
function registerNavigation($userId, $shopId, $shopNum, $shopName, $lat, $lng, $arrivalTime, $genre, $image, $url) {
    //到着時間を設定する
    $dbh = dbConnection::getConnection();
    $sql = 'insert into '.TABLE_NAME_NAVIGATION.' (userid, shopid, shopnum, shopname, shop_lat, shop_lng, arrival_time, genre, image, url) 
            values (pgp_sym_encrypt(?, \'' . getenv('DB_ENCRYPT_PASS') . '\'), ?, ?, ?, ?, ?, ?, ?, ?, ?)';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $shopId, $shopNum, $shopName, $lat, $lng, $arrivalTime, $genre, $image, $url));
}
function checkShopByNavigation($userId, $shopNum) {
    $dbh = dbConnection::getConnection();
    $sql = 'select shopid, shopname from ' . TABLE_NAME_NAVIGATION . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') AND ? = shopnum';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $shopNum));
    // if no record
    if (!($row = $sth->fetch())) {
        return PDO::PARAM_NULL;
    } else {
        return $row;
    }
}

//完全にランダムで取り出し
function getRandomByNavigation($userId) {
    //ナビゲーションテーブルからshopidを取り出す
    $dbh = dbConnection::getConnection();
    $sql = 'select * from ' . TABLE_NAME_NAVIGATION . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') order by random() limit 3';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId));
    // $shopIds = $sth->fetchall();
    if (!($data = $sth->fetchAll())) {
        return PDO::PARAM_NULL;
    } else {
        return $data;
    }
}

//設定した雰囲気の中からランダムで取り出し
function getMatchByNavigation($userId, $userAmbi) {
    //ナビゲーションテーブルからshopidを取り出す
    $dbh = dbConnection::getConnection();
    $sql = 'select shopid from ' . TABLE_NAME_NAVIGATION . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId));
    $shopIds = $sth->fetchall();

    $matchShopList = array();
    foreach ($shopIds as $shopId) {
            $sql = 'select review from ' .TABLE_NAME_REVIEWS. ' where ? = shopid and review_num = 2';
            $sth = $dbh->prepare($sql);
            $sth->execute(array($shopId["shopid"]));
            $ambis = $sth->fetchall();
            $ambiList = array();
            foreach ($ambis as $ambi) {
                array_push($ambiList, $ambi["review"]);
            }
            $matchAmbi = return_max_count_item($ambiList);

            if ($userAmbi == $matchAmbi) {
                array_push($matchShopList, $shopId["shopid"]);
            }
    }
    error_log('matchshop:'.print_r($matchShopList, true));
    //↑これは取得できた

    $count = count($matchShopList);
    error_log('count:'.$count);
    
    $showShopList = array();
    if (!($count == 0)) {
        if ($count > 3) {
            $count = 3;
        }
        $randArray = array_rand($matchShopList, $count);
        array_push($showShopList, $matchShopList[$randArray]);
        error_log(print_r($showShopList, true));
    } else {
        return null;
    }


    $sql = 'select * from ' .TABLE_NAME_NAVIGATION. ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') and ? = shopid';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $showShopList[0]));
    $rows = $sth->fetchall();
    return $rows;
}

function getShopDataByNavigation($userId, $shopNum) {
    $dbh = dbConnection::getConnection();
    $sql = 'select * from ' . TABLE_NAME_NAVIGATION . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') AND shopnum BETWEEN ? AND ?';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $shopNum, ($shopNum+4)));
    // if no record
    if (!($data = $sth->fetchAll())) {
        return PDO::PARAM_NULL;
    } else {
        return $data;
    }
}

function deleteNavigation($userId) {
    $dbh = dbConnection::getConnection();
    $sql = 'delete from ' . TABLE_NAME_NAVIGATION . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId));
}

function getGenreByNavigation($userId, $shopId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select genre from ' . TABLE_NAME_NAVIGATION . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') and ? = shopid';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $shopId));
    // if no record
    if (!($row = $sth->fetch())) {
        return PDO::PARAM_NULL;
    } else {
        return $row['genre'];
    }
}


function return_max_count_item($list, &$count = null)
{
    if (empty($list)) {
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
    foreach ($list as $key => $val) {
        if ($before_val > $val) {
            break;
        } else {
            // 個数が同値の場合は配列に追加する
            array_push($no1_list, $key);
            $before_key = $key;
            $before_val = $val;
        }
    }
    $count = $before_val;
    if (count($no1_list) > 1) {
        //同値の場合の処理があればここに書く
        return $no1_list[0];
    } else {
        return $before_key;
    }
}
?>