<?php

function registerEventShopsByOwner($email, $owner, $kind, $shopName, $img, $link, $holdStart, $holdEnd, $openTime, $closeTime, $genre, $feature, $lat, $lng) {
    $dbh = dbConnection::getConnection();
    $sql = 'insert into ' . TABLE_NAME_EVENTSHOPS . ' (userid, owner, kind, event_name, photo, url, open_date, close_date, open_time, close_time, genre, feature, latitude, longitude) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($email, $owner, $kind, $shopName, $img, $link, $holdStart, $holdEnd, $openTime, $closeTime, $genre, $feature, $lat, $lng));
}

function getShopsEventsData($type) {
    $dbh = dbConnection::getConnection();
    $sql = 'select * from ' . TABLE_NAME_EVENTSHOPS . ' where ? = kind';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($type));
    if (!($rows = $sth->fetchall())) {
        return PDO::PARAM_NULL;
    } else {
        return $rows;
    }
}

?>