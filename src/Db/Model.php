<?php

namespace EasyDb;

use EasyDb\Exception\DbException;

/**
 * @author cps_1993@126.com
 *
 */
class Model extends Builder
{
    protected bool $table_name_lower = true;

    /**
     * @throws DbException
     */
    public function __construct()
    {
        $this->prefix = $this->prefix ?: Db::getConfig()->out()['prefix'];
        $this->table = empty($this->table) ? $this->_getTableNameFromClassName() : $this->table;
        $this->table_struct = new Table($this->table, $this->prefix);
        parent::__construct(Db::getDrive(), $this->table, $this->prefix);
    }

    public function page(int $page = 1, int $length = 20): static
    {
        $this->limit(($page - 1) * $length, $length);
        return $this;
    }

    protected function _getTableNameFromClassName(): string
    {
        $table_name = basename(str_replace('\\', '/', get_class($this)));
        if ($this->table_name_lower) $table_name = strtolower($table_name);
        return $table_name;
    }


}