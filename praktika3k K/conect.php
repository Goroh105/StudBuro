<?php

$host = "localhost";
$bd = "studburo";
$bduser = "root";
$bdpass = "";

@$mysqli = new mysqli ($host,$bduser,$bdpass,$bd);

if ($mysqli->connect_error) {
    die('Ошибка подключения (' . $mysqli->connect_errno . '): ' . $mysqli->connect_error);
};

$mysqli->set_charset("utf8");

?>