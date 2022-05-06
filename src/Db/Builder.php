<?php


namespace EasyDb;


use EasyDb\Drive\Drive;
use EasyDb\Drive\MysqlPdoDrive;
use EasyDb\Exception\DbException;
use JetBrains\PhpStorm\Pure;

class Builder extends Query
{
    protected MysqlPdoDrive $mysqlPdoDrive;
    protected array $insert_value = [];
    protected array $insert_param = [];
    protected array $update_value = [];
    protected array $update_param = [];
    protected bool  $INSERT_FLAG = false;
    protected bool  $UPDATE_FLAG = false;
    protected bool  $DELETE_FLAG = false;
    /**
     * @throws DbException
     */
    protected function __construct(Drive $drive, mixed $table)
    {
        static::$drive = $drive;
        $this->mysqlPdoDrive = $drive;
        Table::setDrive($drive);

        $this->table_struct = new Table($table, $this->prefix);
    }

    protected function __clone(): void
    {
    }

    /**
     * @throws DbException
     */
    public static function bind(Drive $drive, $table): Builder
    {
        return new static($drive, $table);
    }

    /**
     * @param string $prefix
     */
    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
//        static::$prefix = $prefix;
    }


    /**
     * 启动事务
     * @return bool
     */
    public function begin(): bool
    {
        return $this->mysqlPdoDrive->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->mysqlPdoDrive->commit();
    }

    public function rollBack(): bool
    {
        return $this->mysqlPdoDrive->rollBack();
    }

    /**
     * @param mixed $input
     * @throws DbException
     */
    public function insert(array ...$input): static
    {
        $FIELDS = empty($this->insert_value) ? static::$table_struct->getFields() : $this->fields;
        for ($i = 0; $i < func_num_args(); $i++) {
            $this->insert_param[] = $this->_input(func_get_args()[$i], $FIELDS, $i);
        }
        $this->insert_value = empty($insert_param = array_keys($this->insert_param[0]))?$FIELDS:$insert_param;
        $this->INSERT_FLAG = true;
        return $this;
    }


    /**
     * @throws DbException
     */
    public function update(array $input): static
    {
        $FIELDS = empty($this->fields) ? static::$table_struct->getFields() : $this->fields;

        $this->update_param = $this->_input($input,$FIELDS,0);

        $this->update_value = empty($this->fields) ? array_keys($this->update_param) : $this->fields;
        $this->UPDATE_FLAG = true;
        return $this;
    }

    public function delete()
    {

    }

    /**
     * @throws DbException
     */
    public function apply(): Result|array
    {
        $result = new Result([]);
        if($this->INSERT_FLAG){
            $fields = empty($this->fields)?$this->insert_value:$this->fields;
            $execute_sql = $this->_insert_sql($this->getTable(),$fields);
            $result->addResult(static::$drive->executeQuery($execute_sql,$this->insert_param));
        }
        if($this->UPDATE_FLAG){
            $set = '';
            foreach ($this->update_value as $val){$set.="$val=?, ";}
            $set = rtrim($set,' ,');
            $update_sql = $this->_update_sql($this->getTable(),$set,$this->where_para);
            empty($this->where_para) && throw new DbException('Conditions must apply');
            $result->addResult(static::$drive->executeQuery($update_sql,[array_merge(array_values($this->update_param),$this->bind_params)]));
        }
        $this->INSERT_FLAG = $this->UPDATE_FLAG = $this->DELETE_FLAG = false;
        return $result;

    }


    /**
     * @param array $input
     * @param array $fields
     * @param int $idx
     * @param int $type 限定类型: 0 不限定 1.索引数组，2.关联数组 , 参数：如果不为指定数组类型，返回null
     * @return array|null
     * @throws DbException
     */
    protected function _input(array $input, array $fields, int $idx,int $type =0): ?array
    {
        $model = null;
        foreach ($input as $k => $v) {
            is_null($model) && $model = is_numeric($k) ? 1 : 2;
            (is_numeric($k) ? 1 : 2) !== $model &&
            throw new DbException("input type error:array[$idx]");
        }
        if($type && $model !== $type)return null;
        if($model == 1){
            if(count(array_values($fields)) != count(array_values($input)))
                throw new DbException("must have the same number of elements");
            return array_combine(array_values($fields),array_values($input));
        }
        else if($model == 2){
            return $input;
        }else
            throw new DbException("Illegal input");
    }

    protected function _insert_sql(string $table_name, array $fields): string
    {
        $FIELD = '`' . implode('`,`', $fields) . '`';
        $VALUE = ':' . implode(',:', $fields);
        return "INSERT INTO $table_name($FIELD) VALUES ($VALUE)";
    }

    protected function _update_sql(string $table_name,string $set,string $where =null):string
    {
        $update_sql = "UPDATE $table_name SET $set ";
        $where && $update_sql.= "WHERE $where";
        return $update_sql;
    }

}