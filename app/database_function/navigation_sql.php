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

function getRandomByNavigation($userId) {
    //ナビゲーションテーブルからshopidを取り出す
    $sql = 'select * from ' . TABLE_NAME_NAVIGATION . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') order by rand() limit 3';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId));
    $shopIds = $sth->fetchall();
    if (!($data = $sth->fetchAll())) {
        return PDO::PARAM_NULL;
    } else {
        return $data;
    }
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

?>