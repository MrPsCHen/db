<?php


namespace EasyDb\Drive;
use EasyDb\Config\config;
use EasyDb\Exception\DbException;
use PDO;
use PDOException;

class MysqlPdoDrive implements Drive
{
    protected           Config  $Config;
    protected           ?PDO    $pdo            = null;
    protected static    string  $charset        = 'utf8';
    protected static    int     $affected_rows  = 0;
    protected static    string  $error_msg      = '';
    protected static    string  $error_code     = '0';

    /**
     * @throws DbException
     */
    public function connect(): ?PDO
    {
        if(!$this->Config){
            throw new DbException();
        }
        $config = $this->Config->out();
        $dsn = "mysql:dbname={$config['database']};host={$config['host']}";
        try {
            $pdo = $this->pdo = $this->pdo ?? new PDO($dsn,$config['username'],$config['password']);
            $pdo->exec("set names ".self::$charset);
            return $pdo;
        }catch (PDOException $e) {
//            var_dump($e);
            echo "";
        }

        return null;
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
        return true;
    }

    /**
     * @throws DbException
     */
    public function baseQuery(string $sql): array
    {
        self::connect();
        if($this->pdo){
            $instance           = $this->pdo->query($sql);
            self::$error_code   = $this->pdo->errorCode();
            self::$error_msg    = json_encode($this->pdo->errorInfo());
            if($instance){
                return $instance->fetchAll(PDO::FETCH_ASSOC);
            }
            return [];
        }else{
            throw new DbException('not connection');
        }
    }

    /**
     * @throws \EasyDb\Exception\DbException
     */
    public function executeQuery(string $sql, array $array): bool
    {
        self::connect();
        self::$affected_rows    = 0;
        self::$error_msg        = '';
        self::$error_code       = 0;
        $instance               = $this->pdo->prepare($sql);
        $back                   = $instance->execute();
        self::$affected_rows    = $instance->rowCount();
        self::$error_code       = $instance->errorCode();
        self::$error_msg        = json_encode($instance->errorInfo());
        return $back;
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

    public static function getErrorCode(): int
    {
        if(is_numeric(self::$error_code)){
            return (int)self::$error_code;
        }else {
            return -2;
        }
    }

    public static function getErrorMessage(): string
    {
        return self::$error_msg;
    }
}