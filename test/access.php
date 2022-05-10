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
    public string $table = "admin_user";

}
class test extends Model{
    public string $table = "test";
}


$model = new access();

var_export($model->where('id',1)->update(['password'=>222])->apply());

//$test = new test();
//var_export($model->where(['id'=>1])->field(['username','avatar','nickname','wechat_id','alipay_id','status'])->orderBy('id',Query::DESC)->count(true));
//var_export($model->where(['username'=>'admin'])->find());

