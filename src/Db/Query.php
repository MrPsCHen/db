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
    protected static string $sql_2  = 'FROM [$TABLE]';
    protected static string $sql_3  = 'WHERE [$WHERE]';
    protected static string $sql_4  = 'GROUP BY [$GROUP_BY]';
    protected static string $sql_5  = 'ORDER BY [$ORDER_BY]';

    protected static        $input_field;
    protected static string $modem_where = '';
    protected static string $modem_field = '*';

    public function __construct($table)
    {
        self::$table = $table;
    }


    public function select(): Query
    {

        $sql = self::formatField();
        $sql.=str_replace('[$TABLE]',self::$table,self::$sql_2);
        if(self::$build)return $sql;
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
        //TODO 查询条件
        if(is_string($condition) && strlen($condition)>0)
            self::$modem_where = $condition;
        else
            self::$modem_where = self::formatWhere($condition);
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





    protected static function formatWhere($where): string
    {

        return '';
    }


    /**
     *
     */
    protected static function formatField()
    {
        return str_replace('[$FIELD]',self::$modem_field,self::$sql_1);
    }























}