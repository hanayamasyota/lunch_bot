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

?>