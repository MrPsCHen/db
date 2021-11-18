<?php
include "./vendor/autoload.php";
$db     = new \EasyDb\Db();
$config = \EasyDb\Config\MysqlConfig::set('192.168.200.3','iot','root','root');
$drive  = new \EasyDb\Drive\MysqlPdoDrive();
\EasyDb\Db::setConfig($config);
\EasyDb\Db::setDrive($drive);


\EasyDb\SqlString::setDrive($drive);

$str = new \EasyDb\SqlString();
try {
    $str->setTable('user');
} catch (\EasyDb\Exception\DbException $e) {
}


try {
    ($str->where(['id'=>1,['id'=>2,'username'=>"aaa"],['username'=>1],['id','in',[2,3]]])->buildSql()->select());
} catch (\EasyDb\Exception\DbException $e) {
}