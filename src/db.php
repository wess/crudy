<?php
//
// db.php
// crudy
// 
// Author: Wess Cope (me@wess.io)
// Created: 11/15/2021
// 
// Copywrite (c) 2021 Wess.io
//

use Utopia\Database\Database;
use Utopia\Database\Query;
use Utopia\Database\Document;
use Utopia\Database\Adapter\MariaDB;
use Utopia\Cache\Cache;
use Utopia\Cache\Adapter\None as NoCache;

function Data() {
  $dbHost = '127.0.0.1';
  $dbPort = '3306';
  $dbUser = 'root';
  $dbPass = 'maria';
  
  $pdo = new PDO("mysql:host={$dbHost};port={$dbPort};charset=utf8mb4", $dbUser, $dbPass, [
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
    PDO::ATTR_TIMEOUT => 3, // Seconds
    PDO::ATTR_PERSISTENT => true,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  ]);
  
  $cache = new Cache(new NoCache()); // or use any cache adapter you wish
  
  $database = new Database(new MariaDB($pdo), $cache);
  $database->setNamespace('justice_league');
  
  if(!$database->exists()) {
    $database->create();
  }

  $name = 'heroes';

  return $database;
}


function find($database, $name) {
  return $db->find('heroes', [
    new Query('name', Query::EQUALS, $name)
  ]);
}

function all($database) {
  return $database->find('heroes');
}