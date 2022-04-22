<?php

/***
 *
 */
namespace EasyDb;


use EasyDb\Config\Config;
use EasyDb\Drive\Drive;
use EasyDb\Exception\DbException;

class Table implements TableType
{
    protected static    bool    $debug          = true;
    protected static    ?Drive  $drive          = null;
    protected static    ?Config $config         = null;
    protected           array   $alias          = [];
    protected           string  $prefix         = '';
    protected           string  $table          = '';
    protected           array   $full_fields    = [];
    protected           array   $field_full     = [];
    protected           array   $field_param    = [];
    protected           string  $auto_increment = '';
    protected           array   $primary_key    = [];//主键字段
    protected           array   $field_unique   = [];//唯一字段
    protected           array   $field_not_null = [];//字段不为空
    protected           array   $field_default  = [];//默认值
    protected           array   $field_comment  = [];//字段注释

    /**
     * @param string $table
     * @param string $prefix
     * @throws DbException
     */
    public function __construct(string $table = '', string $prefix = '',Drive $drive = null)
    {
        $this->prefix   = $prefix;
        $this->table    = $table;
        $this->format();
    }

    public static function set(){

    }

    public function tableStructure(string $table_name)
    {

    }

    /**
     * @param Drive|null $drive
     */
    public static function setDrive(?Drive $drive): void
    {
        self::$drive = $drive;
    }

    /**
     * @throws DbException
     */
    protected function format(){
        ///查询表结构
        if(!self::$drive)return;
        $table_information = self::$drive->baseQuery("show full fields from " . $this->prefix . $this->table . ";");
        /// 是否抛出异常:
        /// 1.$debug 为true
        /// 2.表结构信息为空
        /// 3.错误代码不为0
        if(self::$debug && empty($table_information) && self::$drive->getErrorCode()){
            throw new DbException(self::$drive->getErrorMessage(),self::$drive->getErrorCode());
        }

        foreach ($table_information as $key => $information)
        {
            if ($information['Key'] == 'PRI') $this->primary_key[$key]      = $information['Field'];
            if ($information['Key'] == 'UNI') $this->field_unique[$key]     = $information['Field'];
            if ($information['Null'] == 'No') $this->field_not_null[$key]   = $information['Field'];
            if ($information['Extra'] == 'auto_increment') $this->auto_increment = $information['Field'];
            $this->field_full[$key]     = $information['Field'];
            $this->field_comment[$key]  = $information['Comment'];
            $this->field_default[$key]  = $information['Default'];
        }
    }

    /**
     * 检查字段是否存在，如果都存在，返回空数组，否则返回不存在的字段
     * @param array $fields 输入的字段
     * @return array 返回不存在的字段
     */
    public function fieldsHas(array $fields):array
    {
        $bad_field = [];
        foreach ($fields as $key => $field) {
            if (in_array($field, $this->field_full) || in_array($key,$this->field_full)) continue;
            $bad_field[$key] = $field;
        }
        return $bad_field;
    }


    public function formatFields(array $fields):string
    {
        $tmp = '';
        foreach ($fields as $key => $field)
        {
            $sqlField = !is_numeric($key) ? "$key` AS `$field" : $field;
            $tmp.= "`$this->prefix$this->table`.`$sqlField`,";
        }
        empty($tmp) && $tmp = '*';
        return rtrim($tmp,',');
    }


    /**
     * @param array $field
     * @return array
     */
    public function unionFiled(array $field = []):array
    {
        if(!empty($this->field_full)) return array_intersect($field,$this->field_full);
        return $field;
    }

    /**
     * @return array
     */
    public function getFieldFull(): array
    {
        return $this->field_full;
    }

    public function getFields(): array
    {
        return array_diff($this->field_full,[$this->auto_increment]);
    }

    /**
     * @return array
     */
    public function getPrimaryKey(): array
    {
        return $this->primary_key;
    }

    /**
     * @param array $alias
     */
    public function setAlias(array $alias): void
    {
        $this->alias = $alias;
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }




}