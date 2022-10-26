<?php

function checkExistsEmail($email, $psword, $name) {
    $dbh = dbConnection::getConnection();
    $sql = 'select email from ' .TABLE_NAME_OWNER. ' where ? = email';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($email));

    $row = $sth->fetch();
    
    if (!isset($row['email'])) {
        //パスワードは暗号化予定
        $sql = 'insert into ' .TABLE_NAME_OWNER. ' (owner_name, email, psword) values (?, ?, ?)';
        $sth = $dbh->prepare($sql);
        $sth->execute(array($name, $email, $psword));

        return 'success';
    }

    return 'failed';
}

function checkEmailPsword($email, $psword) {
    $dbh = dbConnection::getConnection();
    $sql = 'select email, psword from ' .TABLE_NAME_OWNER. ' where ? = email and ? = psword';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($email, $psword));

    $row = $sth->fetch();

    if (isset($row['email']) && isset($row['psword'])) {
        return 'success';
    }

    return 'failed';
}

?>