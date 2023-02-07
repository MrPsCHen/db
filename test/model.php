<?php

use EasyDb\Config\MysqlConfig;
use EasyDb\Db;
use EasyDb\Drive\MysqlPdoDrive;

include "../vendor/autoload.php";

$config = MysqlConfig::set('10.0.0.9','origin','root','root');
$drive  = new MysqlPdoDrive();
$config::setPrefix('app_');
Db::setConfig($config);
Db::setDrive($drive);

$user = new \Test\user();

var_dump($user->count(true));