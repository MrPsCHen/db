<?php


namespace EasyDb;


use EasyDb\Drive\Drive;
use EasyDb\Exception\DbException;
use Exception;
use JetBrains\PhpStorm\Pure;

class Query
{
    const NULL  = "null";
    const DESC  = 'DESC';
    const ASC   = 'ASC';

    const JOIN_TYPE_INNER   = ' INNER JOIN ';
    const JOIN_TYPE_LEFT    = ' LEFT JOIN ';
    const JOIN_TYPE_RIGHT   = ' RIGHT JOIN ';
    const JOIN_TYPE_DEFAULT = ' INNER JOIN ';
    /*--------------------------------------------------------------------------------------------------------------- */
    /** @var Drive|null 驱动对象 由全局加载 */
    protected   static  ?Drive  $drive          = null;
    /** @var Table|null 数据表结构 */
    protected   static  ?Table  $table_struct   = null;
    /** @var Result|null 返回资源 */
    protected           ?Result $result         = null;
    /** @var string 数据表名称 */
    protected   static  string  $table          = '';
    /** @var string 数据表前缀 */
    protected   static  string  $prefix         = '';
    /** @var ?Table 查询主表 */
    protected           ?Table  $master_table   = null;
    protected           array   $join_table     = [];
    /** @var array 绑定参数 */
    protected           array   $bind_params    = [];

    protected           array   $fields         = [];

    /** @var string 查询条件段落 */
    protected           string  $where_para     = '';

    protected           ?array  $limit         = null;

    protected           ?string $order_by      = null;


    /**
     * @throws DbException
     */
    public static function bind(Drive $drive, $table): Query
    {
        self::$drive = $drive;
        self::$table = $table;
        Table::setDrive($drive);
        self::$table_struct = new Table($table,self::$prefix);
        return new self();
    }

    /**
     * @param string $prefix
     */
    public static function setPrefix(string $prefix): void
    {
        self::$prefix = $prefix;
    }


    /**
     * @throws DbException
     */
    public function select(): Result
    {
        $table      = self::$prefix.self::$table;
        $baseSql    = "SELECT {$this->_outField()} FROM $table {$this->_join()} ";
        !empty($this->where_para)   && $baseSql .= "WHERE $this->where_para";
        $this->limit                && $baseSql .= " LIMIT {$this->limit[0]},{$this->limit[1]}";
        $this->order_by             && $baseSql .= "ORDER BY $this->order_by";
        return new Result(self::$drive->baseQuery($baseSql,$this->bind_params));
    }

    /**
     * @throws DbException
     */
    public function find(): Result|array
    {
        $table = self::$prefix.self::$table;
        $baseSql = "SELECT {$this->_outField()} FROM $table {$this->_join()} ";;
        $baseSql.= " WHERE $this->where_para";
        $baseSql.= " LIMIT 0,1";
        $result = self::$drive->baseQuery($baseSql,$this->bind_params);
        return reset($result);
    }

    public function limit(int $index, int $length): static
    {
        $this->limit[0] = $index;
        $this->limit[1] = $length;
        return $this;
    }
    public function orderBy(string $sort = Query::ASC){

    }

    public function getTable(): string
    {
        return self::$table_struct->getPrefix().self::$table_struct->getTable();
    }

    /**
     * @throws Exception
     */
    public function where(mixed $condition, ?string $value = null): static
    {

        if(is_string($condition)){
            if(is_null($value)){
                $this->where_para = $condition;
            }else{
                $this->bind_params[]=$value;
                $this->where_para = "$condition=?";
            }
        }else if(is_array($condition)){
            $this->where_para = $this->_whereEnum($condition);
        }else{
            throw new Exception("类型错误");
        }
        return $this;
    }

    public function field(mixed $field): static
    {
        if(is_string($field)){
            $this->fields = explode(',',$field);
        }else if (is_array($field)){
            $this->fields = array_values($field);
        }
        return $this;
    }

    /**
     * @throws DbException
     */
    public function join(string|array|Table $table, string|array $on = null,string $JoinType = self::JOIN_TYPE_DEFAULT): static
    {

        if(is_string($table) && $table != self::$table){
            Table::setDrive(self::$drive);
            $this->join_table[] = [new Table($table,self::$prefix),$on,$JoinType];
        }else if ($table instanceof Table){
            $table->Drive(self::$drive);
            $this->join_table[] = [$table,$on,$JoinType];
        }
        return $this;
    }



/*--------------------------------------------------------------------------------------------------------------------*/
    /**
     * @param array $condition
     * @param string $logic 关联逻辑:
     * @param bool $first
     * @return string
     */
    protected function _whereEnum(array $condition, string $logic = 'AND', bool $first = true): string
    {
        $where_para = "";
        foreach ($condition as $key => $node) {
            switch ($this->_checkEnumType($node)){
                case 1:
                    $where_para.= "";
                    break;
                case 2:
                    $where_para.= "OR ".$this->_whereEnum($node);
                    break;
                default:
                    $this->bind_params[] = $node;
                    $where_para.= "$logic `{$this->getTable()}`.`$key`=? ";

            }
        }
        $first && $where_para = ltrim($where_para,"$logic ");
        return $where_para;
    }

    protected function _outField():string
    {
        if(empty($this->fields) && empty($this->join_table)){
            return "*";
        }else{
            !empty($this->fields) && self::$table_struct->setShowFields($this->fields);
            $field_full = self::$table_struct->getFieldFull(true);
            foreach ($this->join_table as $option){
                list($table) = $option;
                $field_full = array_merge($field_full,$table->getFieldFull(true));
            }

            return implode(',',$field_full);
        }
    }

    /**
     * @param $node
     * @return int 0:default,1:enum,2:OR连接
     */
    private function _checkEnumType($node): int
    {
        if(is_array($node)){
            if(count($node) == 3){
                list($field,$logic,$value) = array_values($node);
                if($logic instanceof Logic){
                    return 1;
                }
            }
            return 2;
        }

        return 0;
    }

    /**
     * @throws DbException
     */
    private function _join(): string
    {
        $_join              = '';
        $masterTable        = self::$table_struct;
        $masterKey          = $masterTable->getPrimaryKey();
        $masterPrimaryKey   = reset($masterKey);
        $masterField        = $masterTable->getTable().'_'.$masterPrimaryKey;
        foreach ($this->join_table as $table){
            list($tabIns,$on,$type) = array_values($table);
            if($tabIns){
                if(in_array($masterField,$tabIns->getFieldFull())){
                    $_join = "$type `{$tabIns->getPrefix()}{$tabIns->getTable()}` ON ";
                    $_join.= "`{$masterTable->getPrefix()}{$masterTable->getTable()}`.`$masterPrimaryKey` = ";
                    $_join.= "`{$tabIns->getPrefix()}{$tabIns->getTable()}`.`$masterField`";
                }else{
                    throw new DbException("unknown",101);
                }
            }
        }
        return $_join;
    }

}