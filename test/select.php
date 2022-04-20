<?php
namespace Test;
use EasyDb\Config\MysqlConfig;
use EasyDb\Db;
use EasyDb\Drive\MysqlPdoDrive;

include "./vendor/autoload.php";

$db     = new Db();
$config = MysqlConfig::set('10.0.0.9','app','root','root');
$drive  = new MysqlPdoDrive();
$config::setPrefix('app_');
Db::setConfig($config);
Db::setDrive($drive);
var_export(Db::table('access')->where(['access_id'=>1,'id'=>2])->select()->sql_string);