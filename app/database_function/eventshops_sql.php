<?php

function registerEventShopsByOwner($email, $eventMobilestore, $owner, $shopName, $img, $link, $holdStart, $holdEnd, $openTime, $closeTime, $genre, $feature, $lat, $lng) {
    $dbh = dbConnection::getConnection();
    $sql = 'insert into ' . TABLE_NAME_EVENTSHOPS . ' (userid, event_mobilestore, owner, event_name, photo, url, open_date, close_date, open_time, close_time, genre, feature, latitude, longitude) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($email, $eventMobilestore, $owner, $shopName, $img, $link, $holdStart, $holdEnd, $openTime, $closeTime, $genre, $feature, $lat, $lng));
}

?>