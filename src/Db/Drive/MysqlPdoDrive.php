<?php


namespace EasyDb\Drive;
use EasyDb\Config\config;
use EasyDb\Exception\DbException;

class MysqlPdoDrive implements Drive
{
    protected Config $Config;
    protected \PDO  $pdo;

    /**
     * @throws DbException
     */
    public function connect()
    {
        if(!$this->Config){
            throw new DbException();
        }
        $config = $this->Config->out();
        $dsn = "mysql:dbname={$config['database']};host={$config['host']}";

        return $this->pdo = $this->pdo ?? new \PDO($dsn,$config['username'],$config['password']);

    }

    /**
     * @param config $Config
     */
    public function setConfig(Config $Config)
    {
        $this->Config = $Config;
    }

    public function testConnect()
    {
        $config = $this->Config->out();
        $dsn = "mysql:dbname={$config['database']};host={$config['host']}";
        $dsn = "mysql:dbname={$config['database']};host={$config['host']}";
        var_export($this->pdo);
        // TODO: Implement testConnect() method.
    }

    /**
     * @throws DbException
     */
    public function baseQuery(string $sql): array
    {
        self::connect();
        $instance = $this->pdo->prepare($sql);

        if($instance->execute()){
            return $instance->fetchAll(\PDO::FETCH_ASSOC);
        }

        return [];
    }
}