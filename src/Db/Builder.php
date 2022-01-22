<?php


namespace EasyDb;


use EasyDb\Exception\DbException;

class Builder extends Query
{
    public $affected_rows = 0;

    /**
     * @throws \EasyDb\Exception\DbException
     */
    public function __construct($table)
    {
        self::$drive = Db::getDrive();
        self::$table = $table;

    }


    /**
     * @throws \EasyDb\Exception\DbException
     */
    public function update(array $array): bool
    {
        return $this->_update($array);
    }

    /**
     * @param array $array
     * @return int
     * @throws \EasyDb\Exception\DbException
     */
    public function insert(array $array): int
    {
        return self::_insert($array);
    }

    /**
     * @throws \EasyDb\Exception\DbException
     */
    public function delete(): bool
    {
        return $this->_delete();
    }



    public function getErrorCode(): int
    {
        return (self::$drive)::getErrorCode();
    }

    public function getErrorMsg(): string
    {
        return (self::$drive)::getErrorMessage();
    }


    /**
     * 执行插入逻辑
     * @throws \EasyDb\Exception\DbException
     */
    private function _insert(array $data):bool
    {
        if (empty($data)) {
            if (self::$debug) throw new DbException('not found data');
            else return false;
        }
        $database = (self::$drive->getConfig()->out())['database'];
        $insert_sql = sprintf('INSERT INTO `%s`.`%s`', $database, $this->getTable());
        $insert_filed = '';
        $insert_value = '';
        foreach ($data as $key => $value) {
            $insert_filed .= ",`$key`";
            $insert_value .= ",:$key";
        }
        $insert_filed = ltrim($insert_filed, ',');
        $insert_value = ltrim($insert_value, ',');
        $sql = sprintf($insert_sql . "(%s)" . " VALUES(%s)",$insert_filed,$insert_value);
        $execute = self::$drive->executeQuery($sql,$data);
        $this->affected_rows = self::$drive::getAffectedRows();
        if(self::$drive::getErrorCode() !== 0){
            if(self::$debug) throw new DbException(self::$drive::getErrorMessage());
            exit;
        }
        return  $execute;
    }



    /**
     * @throws \EasyDb\Exception\DbException
     */
    private function _delete(): bool
    {
        if(empty($this->where) && self::$debug) {
            throw new DbException('condition not null');
        }
        $database = (self::$drive->getConfig()->out())['database'];
        $sql = sprintf("DELETE FROM `%s`.`%s` WHERE %s",$database,$this->getTable(),$this->where);
        $execute = self::$drive->executeQuery($sql,[]);
        $this->affected_rows = self::$drive::getAffectedRows();
        return  $execute;
    }


    /**
     * @throws \EasyDb\Exception\DbException
     */
    private function _update(array $data): bool
    {
        if(empty($this->where) && self::$debug) {
            throw new DbException('condition not null');
        }
        $database = (self::$drive->getConfig()->out())['database'];
        $update_value = '';
        foreach ($data as $key=>$value){
            $update_value.= ",`{$this->getTable()}`.`$key` = ";
            if(is_numeric($value) || is_string($value)){
                $update_value.=':'.$key;
            }
        }
        $update_value = ltrim($update_value,',');
        $sql = sprintf("UPDATE `%s`.`%s` SET %s WHERE %s",$database,$this->getTable(),$update_value,$this->where);
        self::$drive::getAffectedRows();
        $execute = self::$drive->executeQuery($sql,$data);
        $this->affected_rows = self::$drive::getAffectedRows();
        return  $execute;
    }
}