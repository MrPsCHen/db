<?php


namespace EasyDb\Drive;


use EasyDb\Config\Config;

interface Drive
{
    public          function getConfig():Config;                            //获取配置
    public          function setConfig(Config $config);                     //设置配置
    public          function connect();                                     //连接数据源
    public          function testConnect();                                 //测试连接
    public          function baseQuery(string $sql);                        //基本查询
    public          function executeQuery(string $sql , array $array):bool; //执行查询
    public static   function getAffectedRows():int;                         //影响行数
    public static   function getErrorCode():int;                            //错误代码
    public static   function getErrorMessage():string;                      //错误消息
}