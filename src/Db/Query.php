<?php


namespace EasyDb;


use EasyDb\Drive\Drive;

class Query
{
    const LEFT_JOIN = 'LEFT JOIN';
    const RIGHT_JOIN= 'RIGHT JOIN';
    const INNER_JOIN= 'INNER JOIN';
    protected static string $table  = '';
    protected static ?Drive $drive  = null;
    protected static ?array $back   = null;
    protected static string $sql_1  = 'SELECT [$FIELD] ';
    protected static string $sql_2  = 'FROM [$TABLE] ';
    protected static string $sql_3  = 'WHERE [$WHERE] ';
    protected static string $sql_4  = '[$JOIN_TYPE] [$JOIN_TABLE] ON [$JOIN_ON] ';
    protected static string $sql_5  = 'GROUP BY [$GROUP_BY] ';
    protected static string $sql_6  = 'ORDER BY [$ORDER_BY] ';
    protected static string $sql_7  = 'LIMIT [$idx],[$len] ';
    protected static ?array $table_structure = [];
//    protected static        $input_field;
    /**
     * @var string|array
     */
    protected static        $modem_where    = '';
    protected static string $modem_field    = '*';
    protected static array  $modem_join     = [];
    protected static ?array $join_field     = [];
    protected static ?array $modem_limit    = [];
    protected static ?array $modem_group    = [];
    protected static ?array $modem_order    = [];

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
        $sql.= self::formatGroup();
        $sql.= self::formatOrder();
        $sql.= self::formatLimit();
        if(self::$drive){
            self::$back = self::$drive->baseQuery($sql);
        }
        return $this;
    }

    public function find():?Query
    {
        self::getTableStructure();
        $sql = self::formatField();
        $sql.= str_replace('[$TABLE]','`'.trim(self::$table,'`').'`',self::$sql_2);
        $sql.= self::formatJoin();
        $sql.= self::formatWhere();
        $sql.= " LIMIT 0,1";
        if(self::$drive){
            self::$back = self::$drive->baseQuery($sql);
        }
        return $this;

    }

    public function count(string $field = '*'):int
    {
        self::getTableStructure();
        $sql = 'SELECT count('.$field.") AS `db_count`";
        $sql.= str_replace('[$TABLE]','`'.trim(self::$table,'`').'`',self::$sql_2);
        $sql.= self::formatJoin();
        $sql.= self::formatWhere();

        if(self::$drive){
            $result = self::$drive->baseQuery($sql);
            $result = reset($result);
            return $result['db_count'];
        }
        return -1;
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

            $prefix = self::$table == $table?'':"{$table}_";
            $field  = "`$table`.`{$table_structure[$i]['Field']}`";
            $alias  = "{$prefix}{$table_structure[$i]['Field']}";
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

    /**
     * @param $field array|string
     * @return $this
     */
    public function orderBy($field,$sort = 'ASC'): Query
    {
        if(!empty($field)){
            if(is_string($field)){
                self::$modem_order[$field] = $sort;
            }else if (is_array($field)) {
                self::$modem_order = array_merge(self::$modem_order, $field);
            }
        }
        return $this;

    }
//
    public function groupBy($field): Query
    {
        if(!empty($field)){
            self::$modem_group = is_string($field)?[$field]:$field;
        }
        return $this;
    }
//
    public function limit(int $index,int $length): Query
    {
        self::$modem_limit = [$index,$length];
        return $this;
    }

    public function join($table = '',$on = '',$join_type = self::LEFT_JOIN): Query
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


    /**
     *
     */
    protected static function formatField()
    {
        $out_field = implode(',',self::$join_field);
        if(empty($out_field))return '*';
        return str_replace('[$FIELD]',empty($out_field)?self::$modem_field:$out_field,self::$sql_1);
    }


    protected static function formatGroup()
    {
        $out_field = implode(',',self::$modem_group);
        if(empty($out_field))return '';
        return str_replace('[$GROUP_BY]',$out_field,self::$sql_5);

    }

    protected static function formatLimit()
    {
        if( !isset(self::$modem_limit[0])||
            !isset(self::$modem_limit[1])||
            !is_numeric(self::$modem_limit[0])||
            !is_numeric(self::$modem_limit[1]) )
        {
            return '';
        }else{
            return str_replace(['[$idx]','[$len]'],self::$modem_limit,self::$sql_7);
        }
    }

    protected static function formatOrder()
    {
        $out_field = '';
        foreach (self::$modem_order as $key=>$val)
        {
            $out_field.= "`$key` $val";
        }
        if(empty($out_field)){
            return '';
        }
        return str_replace('[$ORDER_BY]',$out_field,self::$sql_6);
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