<?php
namespace Test;
use EasyDb\Builder;
use EasyDb\Config\MysqlConfig;
use EasyDb\Db;
use EasyDb\Drive\MysqlPdoDrive;
use EasyDb\Exception\DbException;
use EasyDb\Model;
use EasyDb\Table;
use Exception;

include "./vendor/autoload.php";


MysqlConfig::setPrefix('app_');
Db::setConfig(MysqlConfig::set('10.0.0.9','app','root','root'));
Db::setDrive( new MysqlPdoDrive());

class access extends Model
{


}


$model = new access();
//var_export($model->where(['id'=>1])->field(['username','avatar','nickname','wechat_id','alipay_id','status'])->find());
var_export($model->where(['username'=>'admin1'])->find());