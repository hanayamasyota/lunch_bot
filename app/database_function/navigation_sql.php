<?php

//navigationテーブルへのデータ挿入
function registerNavigation($userId, $shopId, $shopNum, $shopName, $lat, $lng, $genre) {
    //到着時間を設定する
    $dbh = dbConnection::getConnection();
    $sql = 'insert into '.TABLE_NAME_NAVIGATION.' (userid, shopid, shopnum, shopname, shop_lat, shop_lng, genre) 
            values (pgp_sym_encrypt(?, \'' . getenv('DB_ENCRYPT_PASS') . '\'), ?, ?, ?, ?, ?, ?)';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $shopId, $shopNum, $shopName, $lat, $lng, $genre));
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
function getShopDataByNavigation($userId, $shopNum) {
    $dbh = dbConnection::getConnection();
    $sql = 'select shopid, shopname, shopnum, shoplat, shoplng from ' . TABLE_NAME_NAVIGATION . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') AND BETEWEEN+';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $shopNum));
    // if no record
    if (!($row = $sth->fetch())) {
        return PDO::PARAM_NULL;
    } else {
        return $row;
    }
}
function deleteNavigation($userId) {
    $dbh = dbConnection::getConnection();
    $sql = 'delete from ' . TABLE_NAME_NAVIGATION . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId));
}

?>