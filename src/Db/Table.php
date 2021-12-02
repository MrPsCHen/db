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
    protected           string  $table          = '';
    protected           string  $prefix         = '';
    protected           array   $full_fields    = [];
    protected           array   $field_full     = [];
    protected           array   $field_param    = [];
    protected           array   $primary_key    = [];//主键字段
    protected           array   $field_unique   = [];//唯一字段
    protected           array   $field_not_null = [];//字段不为空
    protected           array   $field_default  = [];//默认值
    protected           array   $field_comment  = [];//字段注释

    /**
     * @param string $table
     * @param string $prefix
     * @param array $option
     * @throws \EasyDb\Exception\DbException
     */
    public function __construct($table = '',$prefix = '',$option = [])
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
     * @param \EasyDb\Drive\Drive|null $drive
     */
    public static function setDrive(?Drive $drive): void
    {
        self::$drive = $drive;
    }

    /**
     * @throws \EasyDb\Exception\DbException
     */
    protected function format(){
        ///查询表结构
        if(!self::$drive)return;
        $table_information = self::$drive->baseQuery("show full fields from {$this->prefix}{$this->table};");

        ///是否抛出异常:
        /// 1.$debug 为true
        /// 2.表结构信息为空
        /// 3.错误代码不为0
        if(self::$debug && empty($table_information) && self::$drive::getErrorCode()){
            throw new DbException(self::$drive::getErrorMessage(),self::$drive::getErrorCode());
        }

        foreach ($table_information as $key => $information)
        {
            if ($information['Key'] == 'PRI') $this->primary_key[$key]      = $information['Field'];
            if ($information['Key'] == 'UNI') $this->field_unique[$key]     = $information['Field'];
            if ($information['Null'] == 'No') $this->field_not_null[$key]   = $information['Field'];
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
            if (in_array($field, $this->field_full)) continue;
            $bad_field[$key] = $field;
        }
        return $bad_field;
    }


    public function formatFields(array $fields):string
    {
        $tmp = '';
        foreach ($fields as $field)
        {
            $tmp.= "`{$this->prefix}{$this->table}`.`$field`,";
        }
        empty($tmp) && $tmp = '*';
        return rtrim($tmp,',');
    }


    /**
     * @param array $field
     * @return array
     */
    public function unionFiled($field = []):array
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


}