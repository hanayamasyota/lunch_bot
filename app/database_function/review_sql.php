<?php

function registerReview($userId, $shopId, $reviewNum, $review, $time, $shopName) {
    $dbh = dbConnection::getConnection();
    $sql = 'insert into '. TABLE_NAME_REVIEWS . ' (userid, shopid, review_num, review, time, shopname) values (pgp_sym_encrypt(?, \'' . getenv('DB_ENCRYPT_PASS') . '\'), ?, ?, ?, ?, ?) ';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $shopId, $reviewNum, $review, $time, $shopName));
}

function checkExistsReview($userId, $shopId, $reviewNum) {
    $dbh = dbConnection::getConnection();
    $sql = 'select review_no from ' .TABLE_NAME_REVIEWS. ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') AND ? = shopid AND ? = review_num';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $shopId, $reviewNum));
    // if no record
    if (!($row = $sth->fetch())) {
        return PDO::PARAM_NULL;
    } else {
        return $row['review_no'];
    }
}

function getAllUserIdByReviews($shopId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') as id from reviews where ? = shopid order by pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($shopId));
        // if no record
        if (!($row = $sth->fetchall())) {
            return PDO::PARAM_NULL;
        } else {
            return $row;
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
        return $row['review'];
    }
}

function getReviewData($shopId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select review, review_num, time from ' .TABLE_NAME_REVIEWS. ' where ? = shopid order by pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\'), review_num';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($shopId));
    // if no record
    if (!($row = $sth->fetchall())) {
        return PDO::PARAM_NULL;
    } else {
        return $row;
    }
}

function getPageReviewData($shopId, $page) {
    $start = $page * ONE_PAGE - ONE_PAGE;
    $dataLength = ONE_PAGE * 3;
    $dbh = dbConnection::getConnection();
    $sql = 'select review, review_num, time from ' .TABLE_NAME_REVIEWS. ' where ? = shopid order by pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\'), review_num limit 2 offset 0';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($shopId));
    // if no record
    if (!($row = $sth->fetchall())) {
        return PDO::PARAM_NULL;
    } else {
        return $row;
    }
}

function separateReviewData($userId, $shopId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select review from ' .TABLE_NAME_REVIEWS. ' where ? = shopid and ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') order by review_num';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($shopId, $userId));
    // if no record
    if (!($row = $sth->fetchall())) {
        return PDO::PARAM_NULL;
    } else {
        return $row;
    }
}

function getDataByReviews($userId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select * from ' .TABLE_NAME_REVIEWS. ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') order by time';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId));
    // if no record
    if (!($row = $sth->fetchall())) {
        return PDO::PARAM_NULL;
    } else {
        return $row;
    }
}
function getDataCountByReviews($userId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select count(review) as review_count from ' .TABLE_NAME_REVIEWS. ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId));
    // if no record
    if (!($row = $sth->fetch())) {
        return PDO::PARAM_NULL;
    } else {
        return $row["review_count"];
    }
}

function getShopIdByReviews($userId, $shopName) {
    $dbh = dbConnection::getConnection();
    $sql = 'select shopid from ' .TABLE_NAME_REVIEWS. ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') and ? = shopname';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $shopName));
    // if no record
    if (!($row = $sth->fetchall())) {
        return PDO::PARAM_NULL;
    } else {
        return $row;
    }
}

function updateReview($userId, $shopId, $reviewNum, $review, $time) {
    $dbh = dbConnection::getConnection();
    $sql = 'update ' . TABLE_NAME_REVIEWS . ' set (review, time) = (?, ?) where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') and ? = review_num and ? = shopid';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($review, $time, $userId, $reviewNum, $shopId));
}

function deleteReview($userId, $shopId) {
    $dbh = dbConnection::getConnection();
    $sql = 'delete from '. TABLE_NAME_REVIEWS .' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') and ? = shopid';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $shopId));
}
 
?>