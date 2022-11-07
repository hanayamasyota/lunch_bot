<?php

function registerGenre($genreName) {
    $dbh = dbConnection::getConnection();
    $sql = 'insert into '. TABLE_NAME_GENRE . ' (genre_name) values (?)';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($genreName));

    $sql = 'select id from ' . TABLE_NAME_GENRE . ' where ? = genre_name';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($genreName));
    // if no record
    if (!($row = $sth->fetch())) {
        return PDO::PARAM_NULL;
    } else {
        return $row;
}

function getAllGenres() {
    $dbh = dbConnection::getConnection();
    $sql = 'select * from ' . TABLE_NAME_GENRE;
    $sth = $dbh->prepare($sql);
    $sth->execute(array());
    // if no record
    if (!($rows = $sth->fetchall())) {
        return PDO::PARAM_NULL;
    } else {
        return $rows;
}

function checkGenre($genreName) {
    $dbh = dbConnection::getConnection();
    $sql = 'select id from ' . TABLE_NAME_GENRE . ' where ? = genre_name';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($genreName));
    // if no record
    if (!($row = $sth->fetch())) {
        return PDO::PARAM_NULL;
    } else {
        return $row;
    }
}

?>