<?php


namespace EasyDb;


class Model extends Builder
{
    public function __construct()
    {
        parent::$drive = Db::getDrive();
        parent::__construct(basename(str_replace('\\', '/', get_class($this))));
        parent::getTableStructure(self::$table);
    }




}