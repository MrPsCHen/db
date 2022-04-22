<?php


namespace EasyDb\Drive;


use EasyDb\Config\Config;
use EasyDb\Result;

abstract class Drive
{
    abstract public function getConfig():Config;                                //获取配置
    abstract public function setConfig(Config $config);                         //设置配置
    abstract public function connect();                                         //连接数据源
    abstract public function testConnect();                                     //测试连接
    abstract public function baseQuery(string $sql,array $bindParams = []);     //基本查询
    abstract public function executeQuery(string $sql , array $array):Result;   //执行查询
    abstract public function getAffectedRows():int;                             //影响行数
    abstract public function getErrorCode():int;                                //错误代码
    abstract public function getErrorMessage():string;                          //错误消息

}