<?php
function registerReview($userId, $shopId, $reviewNum, $review) {
    $dbh = dbConnection::getConnection();
    $sql = 'insert into '. TABLE_NAME_REVIEWS . ' (userid, shopid, review_num, review) values (pgp_sym_encrypt(?, \'' . getenv('DB_ENCRYPT_PASS') . '\'), ?, ?, ?) ';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $shopId, $reviewNum, $review));
}

function checkExistsReview($userId, $shopId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select userid from' . TABLE_NAME_REVIEWS . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') and ? = shopid';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $shopId));
    // if no record
    if (!($row = $sth->fetch())) {
        return PDO::PARAM_NULL;
    } else {
        //return before_send
        return $row;
    }
}

function getReviewData($userId) {
}

function updateReview($userId) {

}

function deleteReview($userId, $shopId) {
    $dbh = dbConnection::getConnection();
    $sql = 'delete from '. TABLE_NAME_REVIEWS .' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') and ? = shopid';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $shopId, $reviewNum, $review));
}
 
?>