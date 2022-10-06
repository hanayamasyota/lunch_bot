<?php

// get berore_send message by userid
function getBeforeMessageByUserId($userId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select before_send from ' . TABLE_NAME_USERS . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId));
    // if no record
    if (!($row = $sth->fetch())) {
        return PDO::PARAM_NULL;
    } else {
        // if defore_send is NULL
        if ($row['before_send'] == null) {
            return PDO::PARAM_NULL;
        }
        //return before_send
        return $row['before_send'];
    }
}
function getLocationByUserId($userId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select latitude, longitude from ' . TABLE_NAME_USERS . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId));
    // if no record
    if (!($row = $sth->fetch())) {
        return PDO::PARAM_NULL;
    } else {
        if (($row['latitude'] === null) || ($row['longitude'] === null)) {
            return PDO::PARAM_NULL;
        }
        //return location
        return $row;
    }
}

// テーブル内にユーザIDが存在するかを調べる
function getUserIdCheck($userId, $table) {
    $dbh = dbConnection::getConnection();
    $sql = 'select userid from '.$table.' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId));
    // if no record
    if (!($row = $sth->fetch())) {
        return PDO::PARAM_NULL;
    } else {
        //return userId
        return $row['userid'];
    }
}

// entry userinfo
function registerUser($userId, $beforeSend) {
    $dbh = dbConnection::getConnection();
    $sql = 'insert into '. TABLE_NAME_USERS . ' (userid, before_send) values (pgp_sym_encrypt(?, \'' . getenv('DB_ENCRYPT_PASS') . '\'), ?) ';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $beforeSend));
}
// update userinfo
function updateUser($userId, $beforeSend) {
    $dbh = dbConnection::getConnection();
    $sql = 'update ' . TABLE_NAME_USERS . ' set before_send = ? where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($beforeSend, $userId));
}
// 位置情報の設定・更新
function updateLocation($userId, $lat, $lon) {
    $dbh = dbConnection::getConnection();
    $sql = 'update ' . TABLE_NAME_USERS . ' set (latitude, longitude) = (?, ?) where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($lat, $lon, $userId));
}
// 休憩時間の設定・更新
function updateRestTime($userId, $column, $time) {
    $dbh = dbConnection::getConnection();
    $sql = 'update ' . TABLE_NAME_USERS . ' set '.$column.' = ? where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($time, $userId));
}
// ユーザ設定が完了しているかチェック
function checkUsers($userId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select latitude, longitude, rest_start, rest_end from ' . TABLE_NAME_USERS . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId));
    // if no record
    if (!($row = $sth->fetch())) {
        return PDO::PARAM_NULL;
    } else {
        //return shopname
        return $row;
    }
}

// delete userinfo
function deleteUser($userId, $table) {
    $dbh = dbConnection::getConnection();
    $sql = 'delete from '.$table.' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId));
}

function createUser($userId, $beforeSend) {
    //if not exists userid, entry userid
    if(getUserIdCheck($userId, TABLE_NAME_USERS) === PDO::PARAM_NULL) {
        registerUser($userId, $beforeSend);
    } else {
        //if already exists, update
        updateUser($userId, $beforeSend);
    }
}

?>