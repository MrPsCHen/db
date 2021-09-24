<?php
include "./vendor/autoload.php";
include "user.php";

$config = \EasyDb\Config\MysqlConfig::set('192.168.200.3','iot','root','root');
$drive  = new \EasyDb\Drive\MysqlPdoDrive();

\EasyDb\Db::setConfig($config);
\EasyDb\Db::setDrive($drive);

$user = new user();
$user->join('group','group_id');
print_r($user->select()->toArray());
