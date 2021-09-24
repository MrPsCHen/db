<?php


namespace EasyDb;


use EasyDb\Exception\DbException;

class Builder extends Query
{
    private static string $sql_update_1 = 'UPDATE [$TABLE] ';
    private static string $sql_update_2 = 'SET ';
    private static string $sql_insert_1 = 'INSERT INTO [$TABLE] ';
    private static string $sql_insert_2 = ' ';
    private static string $sql_insert_3 = 'VALUES ([$VALUES])';
    private static array  $set_array    = [];

    /**
     * @throws \EasyDb\Exception\DbException
     */
    public function update(array $array = []): string
    {
        self::$query_flag = 2;
        $sql = str_replace('[$TABLE]','`'.trim(self::$table,'`').'`',self::$sql_update_1);
        $sql.= self::formatSet($array);
        $sql.= self::formatWhere();
        self::$sql_update_2 = 'SET ';

        return self::$drive->executeQuery($sql,$array);

    }

    /**
     * @param array $array
     */
    public function insert(array $array)
    {
        if(empty($array))return;
        if(is_numeric(array_keys($array)[0])){
            var_dump(self::$table_field);
        }else{
            $sql_insert_2 = '';
            foreach ($array as $item)
            {
                if(is_numeric($sql_insert_2))$sql_insert_2.=$item;
                else $sql_insert_2.="\"$item\"";
                $sql_insert_2.=',';
            }
            self::$sql_insert_2 = '('.rtrim($sql_insert_2,',').')';
//            echo rtrim($sql_insert_2,',');
//            echo "\n";
//            echo '`'.implode('`,`',array_keys($array)).'`';

        }
        $sql = str_replace('[$TABLE]','`'.trim(self::$table,'`').'`',self::$sql_insert_1);
        $sql.= '('.'`'.implode('`,`',array_keys($array)).'`)';
        $sql.= ' VALUE'.self::$sql_insert_2;
        return self::$drive->executeQuery($sql,[]);

    }

    /**
     * @throws \EasyDb\Exception\DbException
     */
    private function formatSet(array $array):string
    {
        if (empty($array))throw new DbException('无更新字段');
        foreach ($array as $key => $value)
        {
            if(!is_numeric($value))$value = "\"$value\"";
            self::$sql_update_2.= $key.' = '.$value.' ,';
        }
        self::$sql_update_2 = rtrim(self::$sql_update_2,',');
        return self::$sql_update_2;
    }
    private function formatValue()
    {

    }




}