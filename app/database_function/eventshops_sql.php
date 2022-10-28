<?php

function registerEventShopsByOwner($email, $owner, $shopName, $img, $link, $holdStart, $holdEnd, $openTime, $closeTime, $genre, $feature, $eventMobilestore, $lat, $lng) {
    $dbh = dbConnection::getConnection();
    $sql = 'insert into ' . TABLE_NAME_EVENTSHOPS . ' (userid, owner, event_name, photo, url, open_date, close_date, open_time, close_time, genre, feature, event_mobilestore, latitude, longitude) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($email, $owner, $shopName, $img, $link, $holdStart, $holdEnd, $openTime, $closeTime, $genre, $feature, $eventMobilestore, $lat, $lng));
}

?>