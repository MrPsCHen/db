<?php


namespace EasyDb;


use EasyDb\Drive\Drive;
use EasyDb\Exception\DbException;
use Exception;

class Query
{


    public      static  bool    $debug          = true;

    protected   static  ?Drive  $drive          = null;

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
    protected   array   $group      = [];
    protected   array   $order      = [];



    /**
     * 查询条件
     * @var array
     */
    protected   array   $conditions = [];

    protected   array   $result     = [];

    /**
     * @throws \EasyDb\Exception\DbException
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
     * 1637293294
     * @return $this
     */
    public function select(): Query
    {
        $field = self::$table_struct->formatFields($this->field);
        $sql = sprintf('SELECT %s FROM %s', $field, self::$table);


        !empty($this->where) && $sql.= " WHERE $this->where";
        $sql.= self::_limit();
        $this->result = self::$drive->baseQuery($sql);
        return $this;
    }

    public function find()
    {
        $field = self::$table_struct->formatFields($this->field);
        $sql = sprintf('SELECT %s FROM %s', $field, $this->getFullTableName());
        !empty($this->where) && $sql.= " WHERE $this->where";
        $sql.= " LIMIT 0,1";
        $out = self::$drive->baseQuery($sql);
        return reset($out);
    }

    public function toArray(): array
    {
        return $this->result;
    }


    public function limit($idx = 0,$len = 10): Query
    {
        $this->limit = [$idx,$len];
        return $this;
    }



    /**
     * @throws \EasyDb\Exception\DbException
     */
    public function tableStruct(): Table
    {
        Table::setDrive(self::$drive);
        return self::$table_struct = new Table(self::$table,self::$prefix);
    }

    /**
     * @param array|string $field 要显示的字段
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
        return $this;
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
     *
     * @param array|string $conditions 查询条件
     */
    public function where($conditions = []): Query
    {
        if (is_string($conditions)) {//处理字符串查询条件
            $this->where = self::formatConditionsString($conditions);
        } else if(is_array($conditions)){
            ///处理数组查询条件
            !empty($where = self::formatConditionsArray($conditions)) && $this->where = $where;
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
     * @throws \EasyDb\Exception\DbException
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
     * 格式化查询条件数组
     * @param $condition
     * @param string $logic
     * @return string
     */
    protected function formatConditionsArray($condition,$logic = 'AND'): string
    {
        $template = '';

        foreach ($condition as $key => $item) {
            if(is_array($item) && array_sum(array_keys($item))>=3){
                $template.= $logic." $item[0] $item[1] ";
                if(is_numeric($item[2])){
                    $template.= $item[2];
                }else if(is_string($item[2])){

                    $template.= "\"$item[2]\"";
                }else{
                    $val_str = '';
                    foreach ($item[2] as $value){
                        if(is_numeric($value))$val_str.=$value.',';
                        if(is_string($value))$val_str.="\"$value\",";
                    }
                    $val_str = rtrim($val_str,',');
                    $template.= '('.$val_str.')';
                }
                switch ($item[1]){
                    case 'in':
                        return ltrim($template,'OR AND');
                    case 'like':
                    default:

                }
            }else if(is_array($item)){
                $template.= 'OR ';
                count($item)>1 && $template.= '(';
                $template.= $this->formatConditionsArray($item);
                count($item)>1 && $template.= ') ';
            }else{
                if(!is_numeric($item))$item = "\"$item\"";
                $template.= $logic." $key = $item ";
            }

        }

        return ltrim($template,'OR AND');
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
        return sprintf("`%s`.%s%s",self::$drive->getConfig()->getDataBase(),self::$prefix,self::$table);
    }



}