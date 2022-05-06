<?php
namespace EasyDb;

use EasyDb\Exception\DbException;

/**
 * @author cps_1993@126.com
 *
 */
class Model extends Builder
{
    protected   bool    $table_name_lower   = true;

    /**
     * @throws DbException
     */
    public function __construct()
    {
        static::$prefix       = Db::getConfig()->out()['prefix'];
        static::$table        = empty(static::$table)?$this->_getTableNameFromClassName():static::$table;
        static::$table_struct = new Table(static::$table,static::$prefix);
        parent::__construct(Db::getDrive(),static::$table);
    }
    protected function _getTableNameFromClassName(): string
    {
        $table_name = basename(str_replace('\\', '/', get_class($this)));
        if($this->table_name_lower) $table_name = strtolower($table_name);
        return $table_name;
    }

}