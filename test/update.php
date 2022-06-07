<?php
namespace Test;
use EasyDb\Config\MysqlConfig;
use EasyDb\Db;
use EasyDb\Drive\MysqlPdoDrive;
use EasyDb\Exception\DbException;
use EasyDb\Logic;
use EasyDb\Table;

include "./vendor/autoload.php";

$config = MysqlConfig::set('10.0.0.9','app','root','root');
$drive  = new MysqlPdoDrive();
$config::setPrefix('app_');
Db::setConfig($config);
Db::setDrive($drive);


$out = Db::build('test')->where(['id','<=',10])->update(['test'=>1])->apply();
var_export($out);