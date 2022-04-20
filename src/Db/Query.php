<?php


namespace EasyDb;


use EasyDb\Drive\Drive;
use EasyDb\Exception\DbException;
use Exception;

class Query
{
    const JOIN_TYPE_INNER   = ' INNER JOIN ';
    const JOIN_TYPE_LEFT    = ' LEFT JOIN ';
    const JOIN_TYPE_RIGHT   = ' RIGHT JOIN ';
    const JOIN_TYPE_DEFAULT = ' INNER JOIN ';
    /*--------------------------------------------------------------------------------------------------------------- */
    /**
     * @var bool 报错捕获输出
     */
    public      static  bool    $debug          = true;

    /**
     * @var bool 是否输出sql语句
     */
    public              bool    $is_out_sql     = false;
    public              string  $sql_string     = '';
    /**
     * @var \EasyDb\Drive\Drive|null 驱动对象 由全局加载
     */
    protected   static  ?Drive  $drive          = null;

    /**
     * @var \EasyDb\Table|null 数据表结构
     */
    protected   static  ?Table  $table_struct   = null;

    /**
     * @var string 数据表名称
     */
    protected   static  string  $table      = '';
    /**
     * @var string 数据表前缀
     */
    protected   static  string  $prefix     = '';
    /**
     * @var array 查询字段
     */
    protected   array   $field      = [];
    /**
     * @var string 插叙你条件
     */
    protected   string  $where      = '';
    /**
     * @var array [offset,length]
     */
    protected   array   $limit      = [];
    /**
     * @var array
     */
    protected   array   $group      = [];
    /**
     * @var array
     */
    protected   array   $order      = [];



    /**
     * 查询条件
     * @var array
     */
    protected   array   $conditions = [];

    protected   array   $result     = [];
    /*--------------------------------------------------------------------------------------------------------------- */
    //join  关联表
    protected  array   $join_object = [];

    /*--------------------------------------------------------------------------------------------------------------- */
    //初始化方法
    /**
     * @throws DbException
     */
    public static function bind(Drive $drive, $table): Query
    {
        self::$drive = $drive;
        self::$table = $table;
        $instance = new self();
        $instance->setTable($table);
        return $instance;
    }


    /**
     * 查询方法
     * @return $this
     */
    public function select(): Query
    {
        /** 导出格式化后的字段 */
        $output_field = self::$table_struct->formatFields($this->field);
        /** 拼装基本sql语句 */
        $sql = sprintf('SELECT %s FROM %s', $output_field, $this->getFullTableName());
        /** 拼接查询条件 */
        !empty($this->where) && $sql.= " WHERE $this->where";
        /** 拼接限制子句 */
        $sql.= self::_limit();
        /** 输出模式,如果为is_out_sql true 不执行查询*/
        $this->sql_string = $sql;
        $this->result = self::$drive->baseQuery($sql);
        return $this;
    }

    public function find()
    {
        /** 导出格式化后的字段 */
        $output_field = self::$table_struct->formatFields($this->field);
        /** 拼装基本sql语句 */
        $sql = sprintf('SELECT %s FROM %s', $output_field, $this->getFullTableName());
        /** 拼接查询条件 */
        !empty($this->where) && $sql.= " WHERE $this->where";
        /** 限制一条数据 */
        $sql.= " LIMIT 0,1";
        $this->sql_string = $sql;
        $out = self::$drive->baseQuery($sql);
        return reset($out);
    }

    /**
     *
     * @return int
     */
    public function count():int
    {
        $sql = "SELECT count(*) AS `COUNT_FIELD` FROM {$this->getTable()}";
        !empty($this->where) && $sql.= " WHERE $this->where";
        if($this->is_out_sql)return $sql;
        $out = self::$drive->baseQuery($sql);
        !empty($out) && $out = reset($out);
        return $out['COUNT_FIELD'] ?? 0;
    }

//    /**
//     * 连表查询
//     * @param \EasyDb\Table | \EasyDb\Query | string $table 关联表
//     * @param array | string $field_mapping 映射字段,
//     * @throws \EasyDb\Exception\DbException
//     */
//    public function join($table,$field_mapping = [],$join_type = self::JOIN_TYPE_DEFAULT): Query
//    {
//        if ($table instanceof Table) {
//
//        } else if ($table instanceof Query) {
//
//        } else if (is_string($table)) {
//
//        } else {
//            if (self::$debug) throw new DbException('the input is not accepted');
//        }
//        return $this;
//    }

    public function toArray(): array
    {
        return $this->result;
    }

    /**
     * @param int $idx 索引
     * @param int $len 长度
     * @return $this
     */
    public function limit($idx = 0,$len = 10): Query
    {
        $this->limit = [$idx,$len];
        return $this;
    }


    /**
     * @throws DbException
     */
    public function tableStruct(): Table
    {
        Table::setDrive(self::$drive);
        return self::$table_struct = new Table(self::$table,self::$prefix);
    }

    /**
     * @param array|string $field 指定字段
     * @return $this
     */
    public function field($field): Query
    {
        switch (gettype($field))
        {
            case 'string':
                $this->field = explode(',',$field);
                break;
            case 'array':
                $this->field = $field;
                break;
            default:
        }
        try {
            if(self::$table_struct && !empty($fields = self::$table_struct->fieldsHas($this->field))){
                ///如果开启DEBUG 直接报错
                if(self::$debug) {
                    throw new DbException('字段不存在["' . implode('","', $fields) . '"]', -1);
                }
                ///如果没有 过滤
                foreach ($fields as $key => $item)
                {
                    if(in_array($item,$fields))unset($this->field[$key]);
                }
            }else{
                if(self::$debug){
                    throw new DbException('无法解析数据表结构');
                }
            }
        }catch (Exception $exception){

        }
        return $this;
    }


    public function buildSql(): Query
    {
        $this->is_out_sql = true;
        return $this;
    }

    /**
     * @return string 导出查询语句
     */
    public function toSqlString(): string
    {
        return $this->sql_string;
    }



    /**
     * 条件查询
     * 数组：
     * ['field'=>'value'] 默认为 等于(=)
     * ['field',$logic,'value'] 当需要使用其他条件时 采用三元素[字段名，判断逻辑，值]
     * 字符串:
     * "filed1 = 1 or filed2 in(1,2,3)" 这种方式将直接作为条件拼装
     * 条件间关联:
     * 一层数组包裹 视作 and
     * 两层数组包裹 视作 or
     * @param array|string $conditions 查询条件
     */
    public function where($conditions = []): Query
    {
        if (is_string($conditions)) {//处理字符串查询条件
            $this->where = self::formatConditionsString($conditions);
        } else if(is_array($conditions)){
            ///处理数组查询条件
            if(array_keys($conditions) === range(0,2))$conditions = [$conditions];
            if(array_keys($conditions))
            !empty($where = self::formatConditionsArray([$conditions])) && $this->where = $where;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return self::$table;
    }

    /**
     * @param string $table
     * @throws DbException
     */
    public function setTable(string $table): void
    {
        self::$table = $table;
        $this->tableStruct();
    }

    /**
     * @return \EasyDb\Drive\Drive|null
     */
    public static function getDrive(): ?Drive
    {
        return self::$drive;
    }

    /**
     * @param \EasyDb\Drive\Drive|null $drive
     */
    public static function setDrive(?Drive $drive): void
    {
        self::$drive = $drive;
    }

    /**
     * 格式化查询条件字符串
     * @param $condition
     * @return mixed
     */
    protected function formatConditionsString($condition)
    {
        return $condition;
    }

    /**
     * 递归 生成条件字符串
     * 格式化查询条件数组
     * @param array $condition 查询条件
     * @param string $logic 连接逻辑 AND OR
     * @return string
     */
    protected function formatConditionsArray(array $condition, string $logic = 'AND'): string
    {

        $temp = '';
        foreach ($condition as $key => $item) {
            if(is_string($item)|| is_numeric($item)){
                /// 字符串或数值处理
                $temp.= " $logic $key = ".(is_numeric($item)?$item:"\"$item\" ");
            }else if(is_array($item) && $this->_trinomialCheck($item)){
                /// 判断带逻辑处理的字段处理
                /// [filed,logic,value]
                /// eg: ['user','<>',0]
                if(is_array($item[2])) {
                    $tmp_item = '';
                    foreach ($item[2] as $value) {
                        if(is_numeric($value))$tmp_item.= "$value,";
                        if(is_string($value))$tmp_item.= "\"$value\",";
                    }
                    $item[2] = sprintf("(%s ) ",rtrim($tmp_item,','));
                }
                $temp.= " $logic $item[0] $item[1] $item[2] ";
            }else {
//                $logic = !is_array($condition[0]) ? 'AND' : 'OR';
                $temp_deep = $this->formatConditionsArray($item,"AND");
                count($item)>=2 && $temp_deep = "($temp_deep) ";
                $temp= trim($temp)." OR ".$temp_deep;
            }
        }
        $temp = ltrim($temp,' AND ');
        return ltrim($temp,' OR ');
    }

    /**
     *
     * @param $item mixed 查询字段
     * @return bool
     */
    private function _trinomialCheck($item):bool{
        if(!is_array($item)) {
            return false;
        }else if(array_sum(array_keys($item))<3) {
            return false;
        }
        for($i = 0;$i<=1;$i++){
            if(!is_numeric($item[$i]) && !is_string($item[$i]))return false;
        }
        return true;
    }

    private function _limit(): string
    {
        if(empty($this->limit)){
            return "";
        }
        return " LIMIT {$this->limit[0]},{$this->limit[1]}";
    }


    private function getFullTableName():string
    {
        return sprintf("`%s`.`%s%s`",self::$drive->getConfig()->getDataBase(),self::$prefix,self::$table);
    }


//    private function formatConditionsType(){
//
//    }

}