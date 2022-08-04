<?php
//DATABASE_FUNCTIONS//--------------------------------------------------------------

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
        //return location
        return $row;
    }
}

// userid exists check and return userid
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

// get shopname by shopid and return shopname
function getShopNameByShopId($shopId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select shopid, shopname from ' . TABLE_NAME_SHOPS . ' where ? = shopid';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($shopId));
    // if no record
    if (!($row = $sth->fetch())) {
        return PDO::PARAM_NULL;
    } else {
        //return shopname
        return $row;
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
// update location
function updateLocation($userId, $lat, $lon) {
    $dbh = dbConnection::getConnection();
    $sql = 'update ' . TABLE_NAME_USERS . ' set (latitude = ?, longitude = ?) where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($lat, $lon, $userId));
}

// delete userinfo
function deleteUser($userId, $table) {
    $dbh = dbConnection::getConnection();
    $sql = 'delete from '.$table.' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId));
}

// entry reviewstock
function registerReviewDataFirst($userId, $shopId) {
    $dbh = dbConnection::getConnection();
    $sql = 'insert into '. TABLE_NAME_REVIEWSTOCK . ' (userid, shopid) values (pgp_sym_encrypt(?, \'' . getenv('DB_ENCRYPT_PASS') . '\'), ?) ';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $shopId));
}
function registerReviewData($userId, $column, $data) {
    $dbh = dbConnection::getConnection();
    $sql = 'insert into '.TABLE_NAME_REVIEWSTOCK.' (?) values (?) where ? = pgp_sym_encrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($column, $data, $userId));
}
?>