<?php
namespace Test;
use EasyDb\Builder;
use EasyDb\Config\MysqlConfig;
use EasyDb\Db;
use EasyDb\Drive\MysqlPdoDrive;
use EasyDb\Model;

include "./vendor/autoload.php";

$config = MysqlConfig::set('10.0.0.9','app','root','root');
$drive  = new MysqlPdoDrive();
$config::setPrefix('app_');
Db::setConfig($config);
Db::setDrive($drive);

class access extends Model
{


}


$model = new access();
var_export($model->select()->toArray());
