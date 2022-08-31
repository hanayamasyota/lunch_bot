<?php

function registerUserShopData($userId, $searchRange, $page=0) {
    $dbh = dbConnection::getConnection();
    $sql = 'insert into '. TABLE_NAME_USERSHOPDATA . ' (userid, page_num, shop_length) values (pgp_sym_encrypt(?, \'' . getenv('DB_ENCRYPT_PASS') . '\'), ?, ?) ';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $page, $searchRange));
}
function getDataByUserShopData($userId, $column) {
    $dbh = dbConnection::getConnection();
    $sql = 'select '.$column.' from ' . TABLE_NAME_USERSHOPDATA . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId));
    // if no record
    if (!($row = $sth->fetch())) {
        return PDO::PARAM_NULL;
    } else {
        return $row[$column];
    }
}
function updateUserShopData($userId, $column, $data) {
    $dbh = dbConnection::getConnection();
    $sql = 'update ' . TABLE_NAME_USERSHOPDATA . ' set '.$column.' = ? where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($data, $userId));
}

?>