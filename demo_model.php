<?php
include "./vendor/autoload.php";
include "user.php";

$config = \EasyDb\Config\MysqlConfig::set('10.0.0.10','cloud-master','root','root');
$drive  = new \EasyDb\Drive\MysqlPdoDrive();
\EasyDb\Db::setConfig($config);
\EasyDb\Db::setDrive($drive);

$user = new user('account');
var_export($user->filter(['password'])->timeFormat()->find());





//$user = new user();
////$user->join('group','group_id')->where(['id'=>1]);
//////print_r($user->select()->toArray());
////try {
////    ($user->update(['username' => "ok"]));
////} catch (\EasyDb\Exception\DbException $e) {
////}
////$user->where(['id'=>2])->update(['username'=>'powwow']);
//$password = password_hash(md5('admin'),PASSWORD_BCRYPT );
//var_dump($user->insert(['nickname'=>'管理员','username'=>'username','password'=>$password]));