<?php
function registerReview($userId, $shopId, $reviewNum, $review) {
    $dbh = dbConnection::getConnection();
    $sql = 'insert into '. TABLE_NAME_REVIEWS . ' (userid, shopid, review_num, review) values (pgp_sym_encrypt(?, \'' . getenv('DB_ENCRYPT_PASS') . '\'), ?, ?, ?) ';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $shopId, $reviewNum, $review));
}
function registerReviewWithTime($userId, $shopId, $reviewNum, $review, $time) {
    $dbh = dbConnection::getConnection();
    $sql = 'insert into '. TABLE_NAME_REVIEWS . ' (userid, shopid, review_num, review, time) values (pgp_sym_encrypt(?, \'' . getenv('DB_ENCRYPT_PASS') . '\'), ?, ?, ?, ?) ';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $shopId, $reviewNum, $review, $time));
}

function checkExistsReview($userId, $shopId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select review_no from ' .TABLE_NAME_REVIEWS. ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') AND ? = shopid';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $shopId));
    // if no record
    if (!($row = $sth->fetch())) {
        return PDO::PARAM_NULL;
    } else {
        //return before_send
        return $row['review_no'];
    }
}

function getShopIdByReviews($userId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select shopid from ' .TABLE_NAME_REVIEWS. ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId));
    // if no record
    if (!($row = $sth->fetch())) {
        return PDO::PARAM_NULL;
    } else {
        //return before_send
        return $row['shopid'];
    }
}

function getOwnReviewData($userId, $shopId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select review from ' .TABLE_NAME_REVIEWS. ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') and ? = shopid';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $shopId));
    // if no record
    if (!($row = $sth->fetch())) {
        return PDO::PARAM_NULL;
    } else {
        //return before_send
        return $row['review'];
    }
}

function getReviewData($shopId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select review, review_num, time from reviews where ? = shopid order by pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($shopId));
    // if no record
    if (!($row = $sth->fetchall())) {
        return PDO::PARAM_NULL;
    } else {
        //return before_send
        return $row;
    }
}

function updateReview($userId) {

}

function deleteReview($userId, $shopId) {
    $dbh = dbConnection::getConnection();
    $sql = 'delete from '. TABLE_NAME_REVIEWS .' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') and ? = shopid';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $shopId));
}
 
?>