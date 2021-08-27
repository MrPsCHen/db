<?php


namespace EasyDb;


class Table
{
    protected string $name = '';

    protected  array $field_full = [];
    protected  array $field_param= [];


    public function __construct($table = '',$prefix = '',$option = [])
    {

    }

    public static function set(){

    }

}