<?php
function getDb() {
  $dsn = 'mysql:dbname=temperature;';
  $usr = 'root';
  $passwd = 'root';

    $db = new PDO($dsn, $usr, $passwd);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//    $db = new PDO($dsn, $usr, $passwd, [PDO::ATTR_PERSISTENT => true]);
  return $db;
}