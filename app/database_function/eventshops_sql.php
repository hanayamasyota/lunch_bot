<?php

function registerEventShopsByOwner($email, $owner, $kind, $shopName, $img, $link, $holdStart, $holdEnd, $openTime, $closeTime, $genre, $feature, $lat, $lng) {
    $dbh = dbConnection::getConnection();
    $sql = 'insert into ' . TABLE_NAME_EVENTSHOPS . ' (userid, owner, kind, event_name, photo, url, open_date, close_date, open_time, close_time, genre, feature, latitude, longitude) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($email, $owner, $kind, $shopName, $img, $link, $holdStart, $holdEnd, $openTime, $closeTime, $genre, $feature, $lat, $lng));
}

function getShopsEventsData($type, $page) {
    $start = ($page * ONE_PAGE - ONE_PAGE);
    $dbh = dbConnection::getConnection();
    $sql = 'select * from ' . TABLE_NAME_EVENTSHOPS . ' where ? = kind limit 5 offset ?';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($type, $start));
    if (!($rows = $sth->fetchall())) {
        return PDO::PARAM_NULL;
    } else {
        return $rows;
    }
}
function getOwnShopsEventsData($page, $userId) {
    $start = ($page * ONE_PAGE - ONE_PAGE);
    $dbh = dbConnection::getConnection();
    $sql = 'select * from ' . TABLE_NAME_EVENTSHOPS . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') limit 5 offset ?';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($type, $start));
    if (!($rows = $sth->fetchall())) {
        return PDO::PARAM_NULL;
    } else {
        return $rows;
    }
}

//店ごとのレビュー数取得
function getDataCountByEventShops($type) {
    $dbh = dbConnection::getConnection();
    $sql = 'select count(event_id) as count from ' . TABLE_NAME_EVENTSHOPS . ' where ? = kind';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($type));
    // if no record
    if (!($row = $sth->fetch())) {
        return PDO::PARAM_NULL;
    } else {
        return $row["count"];
    }
}
//店ごとのレビュー数取得
function getOwnDataCountByEventShops($userId) {
    $dbh = dbConnection::getConnection();
    $sql = 'select count(event_id) as count from ' . TABLE_NAME_EVENTSHOPS . ' where ? = userid';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($type));
    // if no record
    if (!($row = $sth->fetch())) {
        return PDO::PARAM_NULL;
    } else {
        return $row["count"];
    }
}

?>