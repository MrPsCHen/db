<?php


namespace EasyDb\Config;


abstract class Config
{

    abstract public function out();
    abstract public function getDataBase():string;
}