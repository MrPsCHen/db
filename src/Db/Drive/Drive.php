<?php


namespace EasyDb\Drive;


use EasyDb\Config\Config;

interface Drive
{
    public function getConfig():Config;
    public function setConfig(Config $config);
    public function connect();
    public function testConnect();
    public function baseQuery(string $sql);

}