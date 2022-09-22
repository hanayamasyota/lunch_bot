<?php
//uservistedshopsテーブルへのデータ挿入
function registerUserVisitedShops($userId, $shopId, $shopName, $time, $shopNum) {
    $dbh = dbConnection::getConnection();
    $sql = 'insert into '. TABLE_NAME_USERVISITEDSHOPS . ' (userid, shopid, shopname, visittime, shopnum) values (pgp_sym_encrypt(?, \'' . getenv('DB_ENCRYPT_PASS') . '\'), ?, ?, ?, ?) ';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $shopId, $shopName, $time, $shopNum));
}

function getUserVisitedShopData($userId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select shopname, visittime, shopnum from'. TABLE_NAME_USERVISITEDSHOPS . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $shopId));
    // if no record
    if (!($row = $sth->fetch())) {
        return PDO::PARAM_NULL;
    } else {
        return $row;
    }
}

//すでに登録されている場合はtimestampを更新する
function updateUserVisitedShops($userId, $shopId, $time) {
    $dbh = dbConnection::getConnection();
    $sql = 'update ' . TABLE_NAME_USERVISITEDSHOPS . ' set visittime = ? where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') and ? = shopid';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($time, $userId, $shopId));
}

function checkUserVisitedShops($userId, $shopId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select * from '. TABLE_NAME_USERVISITEDSHOPS . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') and ? = shopid';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $shopId));
    // if no record
    if (!($row = $sth->fetch())) {
        return PDO::PARAM_NULL;
    } else {
        return $row;
    }
}

function getDataByUserVisitedShops($userId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select shopname visittime shopnum from '. TABLE_NAME_USERVISITEDSHOPS . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $shopId));
    // if no record
    if (!($row = $sth->fetch())) {
        return PDO::PARAM_NULL;
    } else {
        return $row;
    }
}

//10件以上の場合古いものから消去
function countVisitedShops($userId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select count(userid) from '. TABLE_NAME_USERVISITEDSHOPS . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId));
    // if no record
    $count = $sth->fetch();
    return $count;
}

function deleteOldUserVisitedShop($userId) {
    $dbh = dbConnection::getConnection();
    $sql = 'delete from ' . TABLE_NAME_USERVISITEDSHOPS . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') 
        and visittime = (select min(visittime) from  '. TABLE_NAME_USERVISITEDSHOPS . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\'))';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $userId));
}
?>