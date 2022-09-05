<?php
//DATABASE_FUNCTIONS//--------------------------------------------------------------

//uservistedshopsテーブルへのデータ挿入
function registerUserVistedShops($userId, $shopName, $shopId, $shopNum) {
    $dbh = dbConnection::getConnection();
    $sql = 'insert into '. TABLE_NAME_USERVISITEDSHOPS . ' (userid, shopid) values (pgp_sym_encrypt(?, \'' . getenv('DB_ENCRYPT_PASS') . '\'), ?) ';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $shopId));
}
?>