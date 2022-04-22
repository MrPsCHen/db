<?php
namespace Test;
use EasyDb\Config\MysqlConfig;
use EasyDb\Db;
use EasyDb\Drive\MysqlPdoDrive;
use EasyDb\Exception\DbException;

include "./vendor/autoload.php";

$config = MysqlConfig::set('10.0.0.9','app','root','root');
$drive  = new MysqlPdoDrive();
$config::setPrefix('app_');
Db::setConfig($config);
Db::setDrive($drive);
try {
    $result = Db::table('access')->join('collection')->where(['id'=>"1 "])->find();
    var_export($result);
} catch (\Exception $e) {
    var_export($e->getMessage());
}

