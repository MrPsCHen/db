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
    /** @var int 不带前缀 */
    const DEFAULT_FILED_ALIAS  = 1; //不带前缀
    /** @var int 表名前缀 */
    const TABLE_FILED_ALIAS    = 2; //表名前缀
    /** @var int 用户定义 */
    const USER_FILED_ALIAS     = 3; //用户定义前缀

    protected static    bool    $debug          = true;
    protected static    ?Drive  $drive          = null;
    protected static    ?Config $config         = null;
    protected           mixed   $alias          = '';
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

    protected           ?array  $show_fields    = null;//显示字段

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
    public function Drive(Drive $drive){
        self::$drive = $drive;
        $this->format();
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
    public function getFieldFull(bool $field_type = false): array
    {
        if($field_type){
            $field_full = [];
            foreach ($this->field_full as $key => $item){
                if(!is_null($this->show_fields) && !in_array($item,$this->show_fields))continue;
                if(is_string($this->alias)){
                    $field_full[] = "`$this->prefix$this->table`.`$item` AS `$this->alias$item`";
                }elseif(is_array($this->alias) && in_array($key,$this->alias)){
                    $field_full[] = "``$this->prefix$this->table``.`$item` AS `{$this->alias[$key]}$item`";
                }else{
                    break;
                }
            }
            return $field_full;
        }
        return $this->field_full;
    }

    public function getFields(): array
    {
        return array_diff($this->field_full,[$this->auto_increment]);
    }

    public function getAliasFields():array
    {
        return [];
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


    /**
     * @throws DbException
     */
    public function setFieldAliasPrefix(
        int $type = self::DEFAULT_FILED_ALIAS,
        mixed $field_alias_prefix = null
    ): Table
    {
        switch ($type)
        {
            case 1:
                $this->alias = null;
                break;
            case 2:
                $this->alias = $this->prefix.$this->table.'_';
                break;
            case 3:
                !is_string($field_alias_prefix) && throw new DbException("错误类型");
                $this->alias = $field_alias_prefix;
                break;
            default:
                $this->alias = null;
        }
        return $this;

    }

    /**
     * @param array $show_fields
     */
    public function setShowFields(array $show_fields): void
    {
        $this->show_fields = $show_fields;
    }


}