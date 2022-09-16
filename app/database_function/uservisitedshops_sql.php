<?php
//uservistedshopsテーブルへのデータ挿入
function registerUserVistedShops($userId, $shopId, $shopName, $time) {
    $dbh = dbConnection::getConnection();
    $sql = 'insert into '. TABLE_NAME_USERVISITEDSHOPS . ' (userid, shopid, shopname, visitedtime) values (pgp_sym_encrypt(?, \'' . getenv('DB_ENCRYPT_PASS') . '\'), ?, ?, ?) ';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $shopId, $shopName, $time));
}

function getUserVisitedShopData($userId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select shopname, shopnum from'. TABLE_NAME_USERVISITEDSHOPS . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') and ? = shopid';
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
    $sql = 'update ' . TABLE_NAME_USERVISITEDSHOPS . ' set time = ? where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') and ? = shopid';
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

//10件以上の場合古いものから消去
function countVisitedShops($userId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select count(*) from '. TABLE_NAME_USERVISITEDSHOPS . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId));
    // if no record
    if ($sth->fetch() < 0) {
        return PDO::PARAM_NULL;
    } else {
        return $count;
    }
}

function deleteOldUserVisitedShop($userId) {
    $dbh = dbConnection::getConnection();
    $sql = 'delete from ' . TABLE_NAME_USERVISITEDSHOPS . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') 
        and time = (select min(time) from  '. TABLE_NAME_USERVISITEDSHOPS . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId));
}
?>