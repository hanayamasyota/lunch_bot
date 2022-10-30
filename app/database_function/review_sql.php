<?php

function registerReview($userId, $shopId, $reviewNum, $review, $time, $shopName, $conveni) {
    $dbh = dbConnection::getConnection();
    $sql = 'insert into '. TABLE_NAME_REVIEWS . ' (userid, shopid, review_num, review, time, shopname, convenience_store) values (pgp_sym_encrypt(?, \'' . getenv('DB_ENCRYPT_PASS') . '\'), ?, ?, ?, ?, ?, ?) ';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $shopId, $reviewNum, $review, $time, $shopName, $conveni));
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

function getReviewData($shopId, $conveni) {
    $dbh = dbConnection::getConnection();
    $sql = 'select review, review_num, time from ' .TABLE_NAME_REVIEWS. ' where ? = shopid and ? = convenience_store order by pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\'), review_num';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($shopId, $conveni));
    // if no record
    if (!($row = $sth->fetchall())) {
        return PDO::PARAM_NULL;
    } else {
        return $row;
    }
}

function getPageReviewData($userId, $page, $count) {
    $start = ($page * ONE_PAGE - ONE_PAGE) * $count;
    $dataLength = ONE_PAGE * $count;
    $dbh = dbConnection::getConnection();
    $sql = 'select * from ' .TABLE_NAME_REVIEWS. ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') order by time, review_num limit ? offset ?';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $dataLength, $start));
    // if no record
    if (!($row = $sth->fetchall())) {
        return PDO::PARAM_NULL;
    } else {
        return $row;
    }
}
function getPageReviewData2($userId, $page) {
    $start = ($page * ONE_PAGE - ONE_PAGE);
    $dbh = dbConnection::getConnection();
    $sql = 'select distinct shopid from ' .TABLE_NAME_REVIEWS. ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') order by shopid limit 5 offset ?';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId, $start));
    // if no record
    if (!($rows = $sth->fetchall())) {
        return PDO::PARAM_NULL;
    }

    $str = '';
    foreach($rows as $row) {
        if ($row === end($rows)) {
            $str .= "? = shopid";
        } else {
            $str .= "? = shopid or ";
        }
    }

    $sql = 'select * from ' .TABLE_NAME_REVIEWS. ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') and ('.$str.') order by time';
    $sth = $dbh->prepare($sql);
    if (count($rows) == 1) {
        $sth->execute(array($userId, $row[0]["shopid"]));
    }
    if (count($rows) == 2) {
        $sth->execute(array($userId, $row[0]["shopid"], $row[1]["shopid"]));
    }
    if (count($rows) == 3) {
        $sth->execute(array($userId, $row[0]["shopid"], $row[1]["shopid"], $row[2]["shopid"]));
    }
    if (count($rows) == 4) {
        $sth->execute(array($userId, $row[0]["shopid"], $row[1]["shopid"], $row[2]["shopid"], $row[3]["shopid"]));
    }
    if (count($rows) == 5) {
        $sth->execute(array($userId, $row[0]["shopid"], $row[1]["shopid"], $row[2]["shopid"], $row[3]["shopid"], $row[4]["shopid"]));
    }

    $rows = $sth->fetchAll();

    return $rows;
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

//個人のレビュー件数取得
function getDataCountByReviews($userId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select count(distinct shopid) as review_count from ' .TABLE_NAME_REVIEWS. ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId));
    // if no record
    if (!($row = $sth->fetch())) {
        return PDO::PARAM_NULL;
    } else {
        return $row["review_count"];
    }
}

//店ごとのレビュー数取得
function getDataCountByShopReviews($shopId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select count(review) as review_count from ' .TABLE_NAME_REVIEWS. ' where ? = shopid';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($shopId));
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

function conveniDecision($userId, $genre) {

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