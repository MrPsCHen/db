<?php


namespace EasyDb;


use EasyDb\Drive\Drive;
use EasyDb\Exception\DbException;

class SqlString
{

    public      static  bool    $debug          = true;

    protected   static  ?Drive  $drive          = null;

    protected   static  ?Table  $table_struct   = null;

    /**
     * @var string 数据表名称
     */
    protected   string  $table      = '';
    /**
     * @var string 数据表前缀
     */
    protected   string  $prefix     = '';
    /**
     * @var array 查询字段
     */
    protected   array   $field      = [];
    /**
     * @var string
     */
    protected   string  $where      = '';

    /**
     * 查询条件
     * @var array
     */
    protected   array   $conditions = [];




    /**
     * @return $this
     */
    public function select(): SqlString
    {
        $field = self::$table_struct->formatFields($this->field);
        $sql = sprintf('SELECT %s FROM %s', $field, $this->table);

        !empty($this->where) && $sql.= " WHERE $this->where";
        var_export(self::$drive->baseQuery($sql));

        return $this;
    }

    public function find()
    {

    }
















    /**
     * @throws \EasyDb\Exception\DbException
     */
    public function tableStruct()
    {
        Table::setDrive(self::$drive);
        self::$table_struct = new Table($this->table,$this->prefix);
    }

    /**
     * @param array|string $field 要显示的字段
     * @return $this
     * @throws \EasyDb\Exception\DbException
     */
    public function field($field): SqlString
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

        if(!empty($fields = self::$table_struct->fieldsHas($this->field))){
            ///如果开启DEBUG 直接报错
            if(self::$debug) {
                throw new DbException('字段不存在["' . implode('","', $fields) . '"]', -1);
            }
           ///如果没有 过滤
            foreach ($fields as $key => $item)
            {
                if(in_array($item,$fields))unset($this->field[$key]);
            }
        }
        return $this;
    }


    public function buildSql(): SqlString
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
    public function where($conditions = []): SqlString
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
        return $this->table;
    }

    /**
     * @param string $table
     * @throws \EasyDb\Exception\DbException
     */
    public function setTable(string $table): void
    {
        $this->table = $table;
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
     */
    protected function formatConditionsArray($condition,$logic = 'AND',$wall = false): string
    {
        $template = '';

        foreach ($condition as $key => $item) {
            if(is_array($item) && array_sum(array_keys($item))>=3){
                $template.= $logic." {$item[0]} {$item[1]} ";
                if(is_numeric($item[2])){
                    $template.= $item[2];
                }else if(is_string($item[2])){

                    $template.= "\"{$item[2]}\"";
                }else{
                    $template.= '('.implode(',',$item[2]).')';
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
                $template.= $this->formatConditionsArray($item,'AND',count($item)>1);
                count($item)>1 && $template.= ') ';
            }else{
                if(!is_numeric($item))$item = "\"$item\"";
                $template.= $logic." $key = $item ";
            }

        }

        return ltrim($template,'OR AND');
    }


}