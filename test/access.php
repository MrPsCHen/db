<?php
namespace Test;
use EasyDb\Builder;
use EasyDb\Config\MysqlConfig;
use EasyDb\Db;
use EasyDb\Drive\MysqlPdoDrive;
use EasyDb\Exception\DbException;
use EasyDb\Logic;
use EasyDb\Model;
use EasyDb\Query;
use EasyDb\Table;
use Exception;

include "./vendor/autoload.php";


MysqlConfig::setPrefix('app_');
Db::setConfig(MysqlConfig::set('10.0.0.9','app','root','root'));
Db::setDrive( new MysqlPdoDrive());

class access extends Model
{
    public string $table = "access_user";

}
//class test extends Model{
//    public string $table = "test";
//}

////$test = new test();
//try {
//    Db::self()->begin();
////    $test->where('id',17)->update(['title'=>'xxxxx'])->apply();
//
//    Db::self()->commit();
//} catch (Exception $e) {
//    Db::self()->rollBack();
//}

$model = new access();

$todayStarTime = strtotime(date('d M Y 00:00:00',time()));
$todayEndTime = strtotime(date('d M Y 23:59:59',time()));

var_export($model->where([
    ['username'=>'13983838592']

])->filter('password')->select());

//$result = $model->where('id',1)->find();
//var_export($result);
//$result = $model->where('id',1)->update(['password'=>222])->apply();
//$test = new test();
//var_export($model->where(['id'=>1])->field(['username','avatar','nickname','wechat_id','alipay_id','status'])->orderBy('id',Query::DESC)->count(true));
//var_export($model->where(['username'=>'admin'])->find());
//var_export($result);

