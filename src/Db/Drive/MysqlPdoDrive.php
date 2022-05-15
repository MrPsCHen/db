<?php


namespace EasyDb\Drive;
use EasyDb\Config\config;
use EasyDb\Db;
use EasyDb\Exception\DbException;
use EasyDb\Result;
use PDO;
use PDOException;

class MysqlPdoDrive extends Drive
{
    protected           Config  $Config;
    protected static    ?PDO    $pdo            = null;
    protected static    string  $charset        = 'utf8';
    protected static    int     $affected_rows  = 0;
    protected static    string  $error_msg      = '';
    protected static    string  $error_code     = '0';
    protected static    array   $last_insert_id = [];

    /**
     * @throws DbException
     */
    public function connect(): ?PDO
    {
        if(!$this->Config){
            throw new DbException('not found configure file');
        }
        $config = $this->Config->out();
        $dsn = "mysql:dbname={$config['database']};host={$config['host']}";
        try {
            $pdo = self::$pdo = self::$pdo ?? new PDO($dsn,$config['username'],$config['password']);
            $pdo->exec("set names ".self::$charset);
        }catch (PDOException $exception){
            self::$error_msg = $exception->getMessage();
            self::$error_code= $exception->getCode();

            return null;
        }
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

    /**
     * @throws DbException
     */
    public function testConnect(): bool
    {

        return (bool)$this->connect();
    }

    /**
     * @throws DbException
     */
    public function baseQuery(string $sql, array $bindParams = []): ?array
    {
        $result = null;
        static::connect();
        $pdo = self::$pdo;
        if($pdo) {
            if (empty($bindParams)) {
                $result = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            } else {

                $pdoIns = $pdo->prepare($sql);

                foreach ($bindParams as $k =>$v)
                {

                    $pdoIns->bindParam($k+1,$bindParams[$k]);
                }
                $pdoIns->execute();
                $result = $pdoIns->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        return $result;
    }

    /**
     * @return bool 提交一个事务
     */
    public function beginTransaction(): bool
    {
        return self::$pdo->beginTransaction();
    }

    /**
     * @return bool 执行事务
     */
    public function commit(): bool
    {

        return self::$pdo->commit();
    }

    /**
     * @return bool 滚回
     */
    public function rollBack(): bool
    {

        return self::$pdo->rollBack();
    }

    /**
     * @throws DbException 执行查询
     */
    public function executeQuery(string $sql, array $array): Result
    {
        self::connect();//连接数据库
        $result = new Result([]);
        foreach ($array as $k => $section){
            $pdo = self::$pdo->prepare($sql); //预处理
            foreach ($section as $kk => $para){
                if(is_numeric($kk)){
                    $pdo->bindParam($kk+1,$array[$k][$kk]);//绑定参数
                }else{
                    $pdo->bindParam(":$kk",$array[$k][$kk]);//绑定参数
                }
            }
            $back = $pdo->execute();

            $result->addResult([
                'status'        => $back,
                'sql'           => $pdo->queryString,
                'errorInfo'     => $pdo->errorInfo(),
                'lastInsertId'  => self::$pdo->lastInsertId(),
                'affectedRows'  => $pdo->rowCount(),
                'params'        => $array,
            ]);
        }
        return $result;
    }
    public function getConfig():config
    {
        return $this->Config;
    }

    /**
     * @return int
     */
    public function getAffectedRows(): int
    {
        return self::$affected_rows;
    }

    public function getErrorCode(): int
    {
        if(is_numeric(self::$error_code)){
            return (int)self::$error_code;
        }else {
            return -2;
        }
    }

    public function getErrorMessage(): string
    {
        return self::$error_msg;
    }



}