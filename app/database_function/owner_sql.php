<?php

function checkExistsEmail($email, $psword, $name) {
    $dbh = dbConnection::getConnection();
    $sql = 'select email from ' .TABLE_NAME_OWNER. ' where ? = email';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($email));

    $row = $sth->fetch();
    
    if (!isset($row['email'])) {
        $sql = 'insert into ' .TABLE_NAME_OWNER. ' (owner_name, email, psword) values (?, ?, ?)';
        $sth = $dbh->prepare($sql);
        $sth->execute(array($name, $email, $psword));

        return 'success';
    }

    return 'failed';
}

?>