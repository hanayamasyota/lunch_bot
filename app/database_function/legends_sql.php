<?php
function registerLegend($userId, $legendId) {
    //取得日時を取得
    $nowTime = time()+32400;
    $nowTimeString = date('Y-m-d H:i:s', $nowTime);

    $dbh = dbConnection::getConnection();
    $sql = 'insert into '. TABLE_NAME_USERLEGENDS . ' (userid, legend_id, got_time) values (pgp_sym_encrypt(?, \'' . getenv('DB_ENCRYPT_PASS') . '\'), ?, ?) ';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $legendId, $nowTimeString));
}

function getUserLegends($userId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select legend_id, legend_name, got_time from ' . TABLE_NAME_USERLEGENDS . 'user_legends where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId));
    if (!($rows = $sth->fetchall())) {
        return PDO::PARAM_NULL;
    } else {
        //return location
        return $rows;
    }
}
?>