<?php


namespace EasyDb;


use EasyDb\Drive\Drive;

class Query
{
    protected static string $table  = '';
    protected static ?Drive $drive  = null;
    protected static ?array $back   = null;
    protected static string $sql_1  = 'SELECT [$FIELD] ';
    protected static string $sql_2  = 'FROM [$TABLE] ';
    protected static string $sql_3  = 'WHERE [$WHERE] ';
    protected static string $sql_4  = '[$JOIN_TYPE] [$JOIN_TABLE] ON [$JOIN_ON] ';
//    protected static string $sql_4  = 'GROUP BY [$GROUP_BY] ';
//    protected static string $sql_5  = 'ORDER BY [$ORDER_BY] ';
    protected static ?array $table_structure = [];
//    protected static        $input_field;
    /**
     * @var string|array
     */
    protected static        $modem_where    = '';
    protected static string $modem_field    = '*';
    protected static array  $modem_join     = [];
    protected static ?array $join_field     = [];

    public function __construct($table)
    {
        self::$table = $table;
    }


    public function select(): ?Query
    {
        self::getTableStructure();

        $sql = self::formatField();
        $sql.= str_replace('[$TABLE]','`'.trim(self::$table,'`').'`',self::$sql_2);
        $sql.= self::formatJoin();
        $sql.= self::formatWhere();

        if(self::$drive){
            self::$back = self::$drive->baseQuery($sql);
        }
        return $this;
    }

    protected static function getTableStructure(?string $table = null)
    {
        $config         = self::$drive->getConfig()->out();
        $database       = $config['database'];
        $table          = empty($table)?(self::$table):$table;
        $table_structure= self::$drive->baseQuery("SHOW FULL COLUMNS FROM `$database`.`$table`");

        $field_mapping  = [];
        for ($i=0;$i<count($table_structure);$i++)
        {
            $field_mapping[$table][$table_structure[$i]['Field']]="`$table`.`{$table_structure[$i]['Field']}`";

            $preifx = self::$table == $table?'':"{$table}_";
            $field  = "`$table`.`{$table_structure[$i]['Field']}`";
            $alias  = "{$preifx}{$table_structure[$i]['Field']}";
            self::$join_field[$alias] = "$field AS `$alias`";
        }
        self::$table_structure = array_merge(self::$table_structure,$field_mapping);

    }

    /**
     * @return array|null
     */
    public function toArray(): ?array
    {
        return self::$back;
    }


    public function where($condition = null): Query
    {
        self::$modem_where = $condition;
        return $this;
    }

//    public function orderBy()
//    {
//
//    }
//
//    public function groupBy()
//    {
//
//    }
//
//    public function limit()
//    {
//
//    }

    public function join($table = '',$on = '',$join_type = 'LEFT JOIN'): Query
    {
        ///数据表规范
        ///1.关联表.主键id = 主查询表.关联字段
        ///2.主查询表.关联表 = [关联表名]_id
        self::$modem_join[]=[$join_type,"`$table`",$on];
        self::getTableStructure($table);
        return $this;
    }

//    public function extra()
//    {
//
//    }



//    public function field()
//    {
//
//    }

//    public function inc()
//    {
//
//    }

//    public function dec()
//    {
//
//    }

//    public function build(): Query
//    {
//        self::$build = true;
//        return $this;
//    }

//    public function count():int
//    {
//
//    }

//    public function find():Query
//    {
//
//    }


    public static function bind(Drive $drive,$table): Query
    {
        $instance = new self($table);
        $instance::setDrive($drive);
        return $instance;
    }

    /**
     * @param Drive $drive
     */
    public static function setDrive(Drive $drive): void
    {
        self::$drive = $drive;
    }


    protected static function formatWhere(): string
    {
        if(is_string(self::$modem_where))
            return self::$modem_where;

        if(is_array(self::$modem_where)){
            return str_replace('[$WHERE]',self::_deep_formatWhere((array)self::$modem_where),self::$sql_3);
        }
        return '';
    }
    protected static function _deep_formatWhere(array $option ,bool $pack_flag = false): string
    {
        $values = array_values($option);
        $keys   = array_keys($option);
        $_where = '';
        for($i=0;$i<count($option);$i++)
        {
            if(is_string($values[$i]) || is_numeric($values[$i])){
                $field = self::_formatField($keys[$i]);
                $_where.= is_numeric($values[$i]) ?"$field=$values[$i] ":"$field=\"$values[$i]\" ";
            }else{
                $flag = false;
                if(is_array($values[$i]) && count($values[$i])==1 && isset($values[$i][0])&& is_array($values[$i][0])){
                    $flag = true;
                }
                $_where.= self::_deep_formatWhere($values[$i],$flag);

            }
            isset($values[$i+1]) &&($_where.= is_array($values[$i+1])?'OR ':'AND ');
        }

        return $pack_flag?"($_where) ":$_where;
    }
    protected static function _formatField(string $field_name)
    {
        foreach (self::$table_structure as $v)
        {
            if(isset($v[$field_name]))return $v[$field_name];
        }
        return $field_name;

    }
    protected static function _outField()
    {
        $field = [];
        var_export(self::$join_field);
        exit;

    }


    /**
     *
     */
    protected static function formatField()
    {
        $out_field = implode(',',self::$join_field);
        return str_replace('[$FIELD]',empty($out_field)?self::$modem_field:$out_field,self::$sql_1);
    }


    private static function formatJoin(): string
    {
        $join = '';
        for ($i=0;$i<count(self::$modem_join);$i++)
        {
            $join.= str_replace(['[$JOIN_TYPE]','[$JOIN_TABLE]','[$JOIN_ON]'],self::$modem_join[$i],self::$sql_4);
            $join.=' ';
        }
        return $join;
    }





















}