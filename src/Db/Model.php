<?php


namespace EasyDb;


class Model extends Builder
{
    /**
     * @throws \EasyDb\Exception\DbException
     */
    public function __construct($table = null)
    {
        parent::$drive = Db::getDrive();
        parent::__construct($table ?? basename(str_replace('\\', '/', get_class($this))));
        parent::bind(parent::$drive, self::$table);
    }

}