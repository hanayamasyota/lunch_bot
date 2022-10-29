<?php
$array = array('a' => 'aiueo0');

$list = [
    'b',
    'c',
];

$count = 0;

for ($i = 0; $i < 2; $i++) {
    array_merge($array, array($list[$i] => 'aiueo'.($i+1)));
    $count += 1;
}

var_dump($array);