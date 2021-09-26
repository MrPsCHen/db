<?php


namespace EasyDb\Drive;
use EasyDb\Config\config;
use EasyDb\Exception\DbException;
use PDO;

class MysqlPdoDrive implements Drive
{
    protected Config $Config;
    protected PDO  $pdo;
    protected static string $charset = 'utf8';
    protected static int $affected_rows = 0;

    /**
     * @throws DbException
     */
    public function connect(): PDO
    {
        if(!$this->Config){
            throw new DbException();
        }
        $config = $this->Config->out();
        $dsn = "mysql:dbname={$config['database']};host={$config['host']}";
        $pdo = $this->pdo = $this->pdo ?? new PDO($dsn,$config['username'],$config['password']);
        $pdo->exec("set names ".self::$charset);
        return $pdo;
    }

    /**
     * @param config $config
     */
    public function setConfig(Config $config)
    {
        $this->Config = $config;
    }

    public function setCharset($charset = 'utf8'){
        self::$charset = $charset;
    }

    public function testConnect(): bool
    {
//        $config = $this->Config->out();
//        $dsn = "mysql:dbname={$config['database']};host={$config['host']}";
        return false;
    }

    /**
     * @throws DbException
     */
    public function baseQuery(string $sql): array
    {
        self::connect();
        $instance = $this->pdo->query($sql);
        if($instance){
            return $instance->fetchAll(PDO::FETCH_ASSOC);
        }
        return [];
    }

    /**
     * @throws \EasyDb\Exception\DbException
     */
    public function executeQuery(string $sql, array $array): bool
    {
        self::$affected_rows = 0;
        self::connect();
        $instance = $this->pdo->prepare($sql);
        if($instance->execute()){
            self::$affected_rows = $instance->rowCount();
            return true;
        }
        return false;
    }
    public function getConfig():config
    {
        return $this->Config;
    }

    /**
     * @return int
     */
    public static function getAffectedRows(): int
    {
        return self::$affected_rows;
    }



}