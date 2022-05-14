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
    protected           ?Table  $table_struct   = null;
    /** @var Result|null 返回资源 */
    protected           ?Result $result         = null;
    /** @var string 数据表名称 */
    protected           string  $table          = '';
    /** @var string 数据表前缀 */
    protected           string  $prefix         = '';
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

    protected           bool    $isToSql       = false;


    /**
     * @throws DbException
     */
    public static function bind(Drive $drive, $table,$prefix = null): Query
    {
        $self = new self();
        static::$drive = $drive;

        Table::setDrive($drive);
        $self->prefix   = $prefix??$drive->getConfig()->out()['prefix'];
        $self->table    = $table;
        $self->table_struct = new Table($table,$self->prefix);
        return $self;
    }

    /**
     * @param string $prefix
     */
    public function setPrefix(string $prefix): void
    {
        $this->$prefix = $prefix;
    }

    /**
     * @throws DbException
     */
    public function select(): Result | string
    {
        $table      = $this->prefix.$this->table;
        $baseSql    = "SELECT {$this->_outField()} FROM $table {$this->_join()} ";
        !empty($this->where_para)   && $baseSql .= "WHERE $this->where_para";
        $this->order_by             && $baseSql .= $this->order_by;
        $this->limit                && $baseSql .= " LIMIT {$this->limit[0]},{$this->limit[1]}";
        if($this->isToSql)return $baseSql;
        return $this->_apply($baseSql,$this->bind_params);
    }

    /**
     * @throws DbException
     */
    public function find(): Result|array |string
    {
        $table      = $this->prefix.$this->table;
        $baseSql    = "SELECT {$this->_outField()} FROM $table {$this->_join()} ";
        !empty($this->where_para)   && $baseSql .= "WHERE $this->where_para";
        $this->order_by             && $baseSql .= $this->order_by;
        $this->limit                && $baseSql .= " LIMIT 0,1";
        if($this->isToSql)return $baseSql;
        return $this->_apply($baseSql,$this->bind_params)->one();
    }

    /**
     * @param bool $realValue
     * @return mixed
     * @throws DbException
     */
    public function count(bool $realValue = false): mixed
    {
        $table      = $this->prefix.$this->table;
        $baseSql    = "SELECT count(*) FROM $table {$this->_join()} ";
        !empty($this->where_para)   && $baseSql .= "WHERE $this->where_para";
        if($this->isToSql)return $baseSql;
        $callback   = $this->_apply($baseSql,$this->bind_params);
        if($realValue){
            return $callback->first();
        }else{
            return $callback;
        }
    }

    /**
     * 答应语句
     * @return $this
     */
    public function toSql(): static
    {
        $this->isToSql = true;
        return $this;
    }

    /**
     * @param int $index
     * @param int $length
     * @return $this
     */
    public function limit(int $index, int $length): static
    {
        $this->limit[0] = $index;
        $this->limit[1] = $length;
        return $this;
    }

    /**
     * @param string $field
     * @param string $sort
     * @return $this
     */
    public function orderBy(string $field,string $sort = Query::ASC): static
    {
        $this->order_by = " ORDER BY `$field` $sort";
        return $this;
    }




    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->prefix.$this->table;
    }

    /**
     * @throws DbException
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

            $this->where_para = $this->_whereEnum([$condition],'OR');
        }else{
            throw new DbException("类型错误");
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

        if(is_string($table) && $table != $this->table){
            Table::setDrive(static::$drive);
            $this->join_table[] = [new Table($table,$this->prefix),$on,$JoinType];
        }else if ($table instanceof Table){
            $table->Drive(static::$drive);
            $this->join_table[] = [$table,$on,$JoinType];
        }
        return $this;
    }

    /**
     * 清理参数
     */
    public function clearParam(): static
    {
        //清空参数
        $this->where_para = '';
        $this->bind_params  = [];
        return $this;
    }

/*--------------------------------------------------------------------------------------------------------------------*/

    protected function _outField():string
    {
        if(empty($this->fields) && empty($this->join_table)){
            return "*";
        }else{
            !empty($this->fields) && $this->table_struct->setShowFields($this->fields);
            $field_full = $this->table_struct->getFieldFull(true);
            foreach ($this->join_table as $option){
                list($table) = $option;
                $field_full = array_merge($field_full,$table->getFieldFull(true));
            }

            return implode(',',$field_full);
        }
    }
    /**
     * @param array $condition
     * @param string $logic 关联逻辑:
     * @return string
     */
    protected function _whereEnum(array $condition, string $logic = 'AND'): string
    {
        $where_para = "";
        foreach ($condition as $key => $node) {
            list($case,$enumString,$enumField) = $this->_checkEnumType($node);
            switch ($case){
                case 2:
                    $where_para.= "OR ".$this->_whereEnum($node);
                    break;
                case 3:
                    if(count($key_param = explode('.',$key))==2){
                        list($table,$field) = $key_param;
                    }else{
                        $table = $this->getTable();
                        $field = &$key;
                    }
                    $where_para.= "$logic `$table`.`$enumField` $enumString ";
                    break;
                default:
                    $this->bind_params[] = $node;
                    if(count($key_param = explode('.',$key))==2){
                        list($table,$field) = $key_param;
                    }else{
                        $table = $this->getTable();
                        $field = &$key;
                    }
                    $where_para.= "$logic `$table`.`$field`= ? ";
            }
        }
        return ltrim($where_para,"$logic ");
    }



    /**
     * @param $node
     * @return array [0:default,1:enum,2:OR连接,附带数据]
     */
    private function _checkEnumType($node): array
    {
        $out_code   = 0;
        $out_string = null;
        $out_field  = null;
        if(is_array($node)){
            if(count($node) == 3){
                list($field,$logic,$value) = array_values($node);
                if(!is_null($logic)&&($logic instanceof Logic || (is_string($logic) && $logic = Logic::tryFrom($logic)))){
                    switch ($logic->name){
                        case Logic::LIKE->name:
                            $out_code   = 3;
                            $out_string = " LIKE $value";
                            break;
                        case Logic::IN->name:
                            $value = is_string($value)?$value:('(\''.implode("','",$value).'\')');
                            $out_code = 3;
                            $out_string = " IN $value";
                            break;
                        default:
                            $out_code = 3;
                            $out_string = " $logic->value $value";
                    }
                    $out_field  = $field;
                }else{
                    //属于是字段查询条件
                    $out_code = 2;
                    $out_field = is_array($field)?2:0;
                }
            }else{
                // 属于是多个条件连接状态
                $out_code = 2;
            }
        }
        return [$out_code,$out_string,$out_field];
    }

    /**
     * @throws DbException
     */
    private function _join(): string
    {
        $_join              = '';
        $masterTable        = $this->table_struct;
        if(!($masterTable instanceof Table))throw new DbException("table not install");
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

    private function _apply(string $baseSql,array $params):Result
    {
        $result = new Result(self::$drive->baseQuery($baseSql,$params));
        $this->clearParam();
        return $result;
    }
}