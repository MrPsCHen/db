<?php


namespace EasyDb\Drive;


use EasyDb\Config\Config;

interface Drive
{
    public          function getConfig():Config;
    public          function setConfig(Config $config);
    public          function connect();
    public          function testConnect();
    public          function baseQuery(string $sql);
    public          function executeQuery(string $sql , array $array):bool;
    public static   function getAffectedRows():int;
    public static   function getErrorCode():int;
    public static   function getErrorMessage():string;
}