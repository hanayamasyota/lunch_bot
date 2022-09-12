<?php
//uservistedshopsテーブルへのデータ挿入
function registerUserVistedShops($userId, $shopId, $shopName, $time) {
    $dbh = dbConnection::getConnection();
    $sql = 'insert into '. TABLE_NAME_USERVISITEDSHOPS . ' (userid, shopid, shopname, visitedtime) values (pgp_sym_encrypt(?, \'' . getenv('DB_ENCRYPT_PASS') . '\'), ?, ?, ?) ';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $shopId));
}
?>