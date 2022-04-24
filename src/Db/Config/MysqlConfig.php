<?php


namespace EasyDb\Config;

class MysqlConfig extends Config
{
    protected           string $host           = '';
    protected           int    $port           = 3306;
    protected           string $database       = '';
    protected           string $username       = 'root';
    protected           string $password       = '';
    protected static    string $prefix         = '';

    public function __toString()
    {
        return $this->host;
    }

    /**
     * @param string $host
     * @param string $db
     * @param string $username
     * @param string $password
     * @param int $port
     * @return MysqlConfig
     */
    public static function set(string $host,string $db,string $username,string $password,int $port = 3306) :MysqlConfig
    {
        $ins = new MysqlConfig();
        $ins->setHost($host);
        $ins->setDatabase($db);
        $ins->setUsername($username);
        $ins->setPassword($password);
        $ins->setPort($port);
        return $ins;
    }

    public static function setPrefix($prefix){
        self::$prefix = $prefix;
    }

    public function pdoDsn(): string
    {
        return "mysql:host={$this->host};port:port={$this->port};dbname={$this->database}";
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @param int $port
     */
    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getDatabase(): string
    {
        return $this->database;
    }

    /**
     * @param string $database
     */
    public function setDatabase(string $database): void
    {
        $this->database = $database;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function out(): array
    {
        return [
            'host'      => $this->getHost(),
            'port'      => $this->getPort(),
            'database'  => $this->getDatabase(),
            'username'  => $this->getUsername(),
            'password'  => $this->getPassword(),
            'prefix'    => self::$prefix
        ];

    }
}