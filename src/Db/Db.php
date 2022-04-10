<?php
namespace EasyDb;

use EasyDb\Config\config;
use EasyDb\Drive;
use EasyDb\Exception\DbException;

class Db
{
    protected static config $config;
    protected static Drive\Drive  $drive;

    public function testConnect()
    {
        if(empty(self::$drive))throw DbException::point(102);
        return self::$drive->testConnect();
    }
    /**
     * @return config
     */
    public static function getConfig(): config
    {
        return self::$config;
    }

    /**
     * @param config $config
     */
    public static function setConfig(config $config): void
    {
        self::$config = $config;
    }

    /**
     * @return Drive\Drive
     * @throws DbException
     */
    public static function getDrive(): Drive\Drive
    {
        if(!isset(self::$drive))throw new DbException('未初始化');
        return self::$drive;
    }

    /**
     * @param Drive\Drive $drive $drive
     */
    public static function setDrive(Drive\Drive $drive): void
    {

        $drive->setConfig(self::$config);
        self::$drive = $drive;
    }


    /**
     * @throws DbException
     */
    public static function table(string $table,$prefix = null):Query
    {
        if($prefix === null){
            $prefix = ((self::$config)->out()['prefix']);
        }else{
            $prefix = '';
        }
        return Query::bind(self::$drive,$table);
    }

    public static function Instance()
    {

    }


}