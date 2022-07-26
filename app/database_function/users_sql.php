<?php
require_once 'legends_sql.php';
define('TABLE_NAME_LEGENDS', 'legends');

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
function getNickNameByUserId($userId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select nickname from ' . TABLE_NAME_USERS . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId));
    // if no record
    if (!($row = $sth->fetch())) {
        return PDO::PARAM_NULL;
    } else {
        return $row['nickname'];
    }
}
function getRestTimeByUserId($userId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select rest_start, rest_end from ' . TABLE_NAME_USERS . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId));
    // if no record
    if (!($row = $sth->fetch())) {
        return PDO::PARAM_NULL;
    } else {
        if (($row['rest_start'] === null) || ($row['rest_end'] === null)) {
            return PDO::PARAM_NULL;
        }
        //return location
        return $row;
    }
}
function getAmbiByUserId($userId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select ambience from ' . TABLE_NAME_USERS . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId));
    // if no record
    if (!($row = $sth->fetch())) {
        return PDO::PARAM_NULL;
    } else {
        //return location
        return $row["ambience"];
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

function countUpPost($userId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select post_times from '.TABLE_NAME_USERS.' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId));
    $row = $sth->fetch()["post_times"]+1;

    //称号判定
    if ($row == 1) {
        //登録
        registerLegend($userId, 1);
    } else if ($row == 5) {
        registerLegend($userId, 2);
    } else if ($row == 10) {
        registerLegend($userId, 3);
    }

    $sql = 'update '.TABLE_NAME_USERS.' set post_times = ? where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($row, $userId));
}
function getCountPost($userId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select post_times from '.TABLE_NAME_USERS.' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId));
    if (!($row = $sth->fetch())) {
        return PDO::PARAM_NULL;
    } else {
        return $row['post_times'];
    }
}

function countUpReview($userId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select review_times from '.TABLE_NAME_USERS.' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId));
    $row = $sth->fetch()["review_times"]+1;

    //称号判定
    if ($row == 1) {
        //登録
        registerLegend($userId, 4);
    } else if ($row == 5) {
        registerLegend($userId, 5);
    } else if ($row == 10) {
        registerLegend($userId, 6);
    }

    $sql = 'update '.TABLE_NAME_USERS.' set review_times = ? where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($row, $userId));
}
function getCountReview($userId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select review_times from '.TABLE_NAME_USERS.' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId));
    if (!($row = $sth->fetch())) {
        return PDO::PARAM_NULL;
    } else {
        return $row['review_times'];
    }
}

function getNowLegend($userId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select now_legend from '.TABLE_NAME_USERS.' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId));
    if (!($row = $sth->fetch())) {
        return PDO::PARAM_NULL;
    } else {
        return $row['now_legend'];
    }
}

function updateNowLegend($userId, $legendId) {
    $dbh = dbConnection::getConnection();
    $sql = 'update ' . TABLE_NAME_USERS . ' set now_legend = ? where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($legendId, $userId));
}

// entry userinfo
function registerUser($userId, $beforeSend) {
    $dbh = dbConnection::getConnection();
    $sql = 'insert into '. TABLE_NAME_USERS . ' (userid, before_send, post_times, review_times) values (pgp_sym_encrypt(?, \'' . getenv('DB_ENCRYPT_PASS') . '\'), ?, 0, 0) ';
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
// 個人設定の更新
function updateRestTime($userId, $start, $end, $ambi, $name) {
    $dbh = dbConnection::getConnection();
    $sql = 'update ' . TABLE_NAME_USERS . ' set (rest_start, rest_end, ambience, nickname) = (?, ?, ?, ?) where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($start, $end, $ambi, $name, $userId));
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

//個人設定が完了しているかチェック
function getPersonalSetting($userId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select rest_start, rest_end, ambience, nickname from ' . TABLE_NAME_USERS . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
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