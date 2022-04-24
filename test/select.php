<?php
namespace Test;
use EasyDb\Config\MysqlConfig;
use EasyDb\Db;
use EasyDb\Drive\MysqlPdoDrive;
use EasyDb\Exception\DbException;
use EasyDb\Table;

include "./vendor/autoload.php";

$config = MysqlConfig::set('10.0.0.9','app','root','root');
$drive  = new MysqlPdoDrive();
$config::setPrefix('app_');
Db::setConfig($config);
Db::setDrive($drive);
try {
    $table = new Table('collection','app_');
    $table->setFieldAliasPrefix(2,'app_');
    $table->setShowFields(['id']);
    $result = Db::table('access')->field('id')->join($table)->where(['id'=>"1 "])->find();
    var_export($result);
} catch (\Exception $e) {
    var_export($e->getMessage());
}
