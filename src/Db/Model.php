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
    protected   array   $format_time        = ['create_time','update_time'];
    protected   bool    $formatTile_flag    = false;
    protected   array   $field_display      = [];
    protected   array   $field_filter       = [];

    /**
     * @throws \EasyDb\Exception\DbException
     */
    public function __construct($table = null)
    {
        parent::$drive = Db::getDrive();
        parent::__construct($table ?? basename(str_replace('\\', '/', get_class($this))));
        parent::bind(parent::$drive, self::$table);
    }


    public function toArray(): array
    {
        if($this->format_time){
            $this->_timeFormat();
        }
        return parent::toArray();
    }

    /**
     * 将要时间戳的字段，格式化为时间字符串
     * @param array $field
     * @return $this
     */
    public function timeFormat($field = ['create_time','update_time']): Model
    {
        $this->formatTile_flag = true;
        !empty($field) && $this->format_time = $field;
        return $this;
    }

    protected function _timeFormat()
    {

        $array_column = [];
        foreach ($this->format_time as $value)
        {
            $array_column[$value] = array_column($this->result,$value);
        }
        foreach ($array_column as $key=>$item){
            foreach ($item as $item_key =>$item_item){
                $array_column[$key][$item_key] = $this->_date($item_item);
                $this->result[$item_key][$key] = $this->_date($item_item);
            }
        }
    }

    protected function _date($time){
        if(strlen($time) == 10){
            return date("Y-m-d H:i:s",$time);
        }
        return $time;
    }

    /**
     * 显示指定字段
     * @param array $filed
     * @return \EasyDb\Model
     */
    public function display(array $filed = []): Model
    {
        $this->field_display = $filed;
        return $this;
    }

    /**
     * 过滤器 不显示部分字段
     * @param array $filed
     * @return $this
     */
    public function filter(array $filed): Model
    {
        $this->field_filter = $filed;
        return $this;
    }


    public function select(): Query
    {
        $this->_output_filed();
        return parent::select();
    }

    public function find()
    {
        $this->_output_filed();
        return parent::find();
    }

    /**
     *
     */
    public function count()
    {
        
    }

    /**
     * 导出字段
     */
    private function _output_filed(): void
    {
        if(empty($this->field_display) && empty($this->field_filter)) {
            return;
        }
        try {
            $this->field = $this->tableStruct()->unionFiled($this->field_display);
            $this->field_filter = $this->tableStruct()->unionFiled($this->field_filter);
            if(empty($this->field)){
                $this->field = $this->tableStruct()->getFieldFull();
            }
            if(!empty($this->field_filter)){
                $this->field = array_diff($this->field,$this->field_filter);
            }
        }catch (DbException $exception){

        }
    }



}