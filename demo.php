<?php

include "./vendor/autoload.php";
/*--------------------------------------------------------------------------------------------------------------------*/
///初始化配置
//$db     = new \EasyDb\Db();
//$config = \EasyDb\Config\MysqlConfig::set('10.0.0.9','app','root','root');
////设置表前缀
//$config::setPrefix('app_');
//$drive  = new \EasyDb\Drive\MysqlPdoDrive();
//\EasyDb\Db::setConfig($config);
//\EasyDb\Db::setDrive($drive);
//try {
//    var_export($db->testConnect());
//} catch (\EasyDb\Exception\DbException $e) {
//    var_export($e);
//}






////
////try {
////    $db->testConnect();
////} catch (\EasyDb\Exception\DbException $e) {
////}
////$ins = \EasyDb\Db::table('user');
//////$back = $ins->where([[['id'=>1,'name'=>1]],['id'=>2]])->select()->toArray();
////\EasyDb\Db::table('user');
////$back = $ins->where(["id"=>1,[["name"=>"john","id"=>1]]]);
////$back->join('group','`group`.`id` = `user`.`group_id`');
////$result = $back->select()->toArray();
////var_export($back->count());
////
////$where1 =
////[
////    'field'=>1,         //AND ()
////    [['field2'=>1,['field3'=>1]]],      //OR
////    ['field5'=>1],
////    'field4'=>1,
////];
////
////$where2 = "`field`=1 AND (`field2`=1 OR `field3`=1) OR field5=1 AND field4 = 1";
//
//// field IN() , field LIKE "" ,
//
//
//
//
//
//
//#count
////$ins = \EasyDb\Db::table('user');
////
////$ins->limit(1,10)->select();
//
//#group
//$ins = \EasyDb\Db::table('user');
//
//$ins->select();
//var_export($ins->toArray());
//




//$mode = new \EasyDb\Model();
//$mode->setTable('user');
//var_export($mode->insert(['nickname'=>'admin','username'=>"admin3"]));
//echo "\n";
//var_export($mode->getErrorCode());
//echo "\n";
//var_export($mode->getErrorMsg());



///

/**
 * 基础查询
 */
//$user = \EasyDb\Db::table('account');
//var_export($user->where([['username'=>'admin']])->select()->toArray());

//var_export($user->where(['id','in',[1,2]])->count());

/**
 * 修改
 */
//$userBuilder = new \EasyDb\Builder('user');
//var_export($userBuilder->where(['id','>=','8'])->update(['nickname'=>"nickname",'avatar'=>'http://']));
/**
 * 新增
 */
//$userBuilder->insert(['nickname'=>11,'username'=>'xxxx']);

/**
 * 删除
 */













