<?php
namespace EasyDb;

use EasyDb\Exception\DbException;

/**
 * @author cps_1993@126.com
 *
 */
class Model extends Builder
{
    /**
     * @var array 字符串时间和时间戳转换
     */
//    protected   array   $format_time        = ['create_time','update_time'];
//    protected   bool    $formatTile_flag    = false;
//    protected   array   $field_display      = [];
//    protected   array   $field_filter       = [];
//    /** @var bool 是否表名全小写 */
    protected   bool    $table_name_lower   = true;

    /**
     * @throws DbException
     */
    public function __construct()
    {
        self::$prefix       = Db::getConfig()->out()['prefix'];
        self::$table        = $this->_getTableNameFromClassName();
        static::$table_struct = new Table(self::$table,self::$prefix);
        parent::__construct(Db::getDrive(),self::$table);
    }



//    /**
//     * @throws DbException
//     */
//    public function __construct($table = null)
//    {
//        self::$table = $table ?? $this->_get_table_name();
//
//        parent::$drive = Db::getDrive();
//
//
//        parent::__construct(parent::$drive->getConfig()->out()['prefix'].self::$table);
//
//        parent::bind(parent::$drive, self::$table);
//    }


//    public function toArray(): array
//    {
//        if($this->format_time){
//            $this->_timeFormat();
//        }
//        return parent::toArray();
//    }
//
//    /**
//     * 将要时间戳的字段，格式化为时间字符串
//     * @param array $field
//     * @return $this
//     */
//    public function timeFormat(array $field = ['create_time','update_time']): Model
//    {
//        $this->formatTile_flag = true;
//        !empty($field) && $this->format_time = $field;
//        return $this;
//    }
//
//    protected function _timeFormat()
//    {
//
//        $array_column = [];
//        foreach ($this->format_time as $value)
//        {
//            $array_column[$value] = array_column($this->result,$value);
//        }
//        foreach ($array_column as $key=>$item){
//            foreach ($item as $item_key =>$item_item){
//                $array_column[$key][$item_key] = $this->_date($item_item);
//                $this->result[$item_key][$key] = $this->_date($item_item);
//            }
//        }
//    }
//
//    protected function _date($time){
//        if(strlen((string)$time) == 10){
//            return date("Y-m-d H:i:s",$time);
//        }
//        return $time;
//    }
//
//    /**
//     * 显示指定字段
//     * @param array $filed
//     * @return \EasyDb\Model
//     */
//    public function display(array $filed = []): Model
//    {
//        $this->field_display = $filed;
//        return $this;
//    }
//
//    public function select(): Query
//    {
//        $this->_output_filed();
//        return parent::select();
//    }
//
//    public function find()
//    {
//        $this->_output_filed();
//        if($out = parent::find()){
//            foreach ($this->format_time as $item){
//                if(isset($out[$item]))$out[$item] = $this->_date($out[$item]);
//            }
//        }
//        return $out;
//    }
//
//
//    /**
//     * 导出字段
//     */
//    private function _output_filed(): void
//    {
//        if(empty($this->field_display) && empty($this->field_filter)) {
//            return;
//        }
//        try {
//            $this->field = $this->tableStruct()->unionFiled($this->field_display);
//            $this->field_filter = $this->tableStruct()->unionFiled($this->field_filter);
//            if(empty($this->field)){
//                $this->field = $this->tableStruct()->getFieldFull();
//            }
//            if(!empty($this->field_filter)){
//                $this->field = array_diff($this->field,$this->field_filter);
//            }
//        }catch (DbException $exception){
//
//        }
//    }
//
//    /**
//     * 获取数据表名称
//     */
//    private function _get_table_name(): string
//    {
//        $table_name = basename(str_replace('\\', '/', get_class($this)));
//        if($this->table_name_lower) $table_name = strtolower($table_name);
//        return $table_name;
//    }



    protected function _getTableNameFromClassName(){
        $table_name = basename(str_replace('\\', '/', get_class($this)));
        if($this->table_name_lower) $table_name = strtolower($table_name);
        return $table_name;
    }

}