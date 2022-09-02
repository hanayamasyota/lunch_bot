<?php
//DATABASE_FUNCTIONS//--------------------------------------------------------------

//uservistedshopsテーブルへのデータ挿入
function registerUserVistedShops($userId, $shopName, $shopId, $shopNum) {
    $dbh = dbConnection::getConnection();
    $sql = 'insert into '. TABLE_NAME_USERVISITEDSHOPS . ' (userid, shopid) values (pgp_sym_encrypt(?, \'' . getenv('DB_ENCRYPT_PASS') . '\'), ?) ';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $shopId));
}

//navigationテーブルへのデータ挿入
function registerNavigation($userId, $shopId, $shopNum, $shopName, $lat, $lng) {
    $dbh = dbConnection::getConnection();
    $sql = 'insert into '.TABLE_NAME_NAVIGATION.' (userid, shopid, shopnum, shopname, shop_lat, shop_lng) 
            values (pgp_sym_encrypt(?, \'' . getenv('DB_ENCRYPT_PASS') . '\'), ?, ?, ?, ?, ?)';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $shopId, $shopNum, $shopName, $lat, $lng));
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
function deleteNavigation($userId) {
    $dbh = dbConnection::getConnection();
    $sql = 'delete from ' . TABLE_NAME_NAVIGATION . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId));
}
?>