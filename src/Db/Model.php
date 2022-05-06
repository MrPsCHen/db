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
        self::$table        = empty(static::$table)?$this->_getTableNameFromClassName():static::$table;
        static::$table_struct = new Table(self::$table,self::$prefix);
        parent::__construct(Db::getDrive(),self::$table);
    }


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





    protected function _getTableNameFromClassName(): string
    {
        $table_name = basename(str_replace('\\', '/', get_class($this)));
        if($this->table_name_lower) $table_name = strtolower($table_name);
        return $table_name;
    }

}