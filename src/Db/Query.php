<?php


namespace EasyDb;


use EasyDb\Drive\Drive;

class Query
{
    protected static string $table  = '';
    protected static ?Drive $drive  = null;
    protected static ?array $back   = null;
    protected static string $field  = '*';
    protected static bool   $build  = false;
    protected static string $sql_1  = 'SELECT [$FIELD] ';
    protected static string $sql_2  = 'FROM [$TABLE] ';
    protected static string $sql_3  = 'WHERE [$WHERE] ';
    protected static string $sql_4  = 'GROUP BY [$GROUP_BY] ';
    protected static string $sql_5  = 'ORDER BY [$ORDER_BY] ';

    protected static        $input_field;
    protected static        $modem_where = '';
    protected static string $modem_field = '*';

    public function __construct($table)
    {
        self::$table = $table;
    }



    public function select(): ?Query
    {
        $sql = self::formatField();
        $sql.= str_replace('[$TABLE]',self::$table,self::$sql_2);
        $sql.= self::formatWhere();
        echo $sql;
        exit;
        if(self::$drive){
            self::$back = self::$drive->baseQuery($sql);
        }
        return $this;
    }

    public function toArray(): ?array
    {
        return self::$back;
    }


    public function where($condition = null): Query
    {
        self::$modem_where = $condition;
        return $this;
    }

    public function orderBy()
    {

    }

    public function groupBy()
    {

    }

    public function limit()
    {

    }

    public function join()
    {

    }

    public function extra()
    {

    }



    public function field()
    {

    }

    public function inc()
    {

    }

    public function dec()
    {

    }

    public function build(): Query
    {
        self::$build = true;
        return $this;
    }




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
                $_where.= is_numeric($values[$i]) ?"{$keys[$i]}={$values[$i]} ":"{$keys[$i]}=\"{$values[$i]}\" ";
            }else{
                $flag = false;
                if(is_array($values[$i]) && count($values[$i])==1 && isset($values[$i][0])&& is_array($values[$i][0])){
                    $flag = true;
                }
                $_where.= self::_deep_formatWhere($values[$i],$flag);

            }
            isset($values[$i+1]) &&($_where.= is_array($values[$i+1])?'OR ':'AND ');
        }

        return $pack_flag?"({$_where}) ":$_where;
    }


    /**
     *
     */
    protected static function formatField()
    {
        return str_replace('[$FIELD]',self::$modem_field,self::$sql_1);
    }























}