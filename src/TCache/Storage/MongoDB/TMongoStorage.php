<?php

namespace TCache\Storage\MongoDB;


trait TMongoStorage
{
    private $host = "localhost";
    private $port = "27017";
    private $dbName = null;
    private $login = "";
    private $password = "";

    private $connected = false;

    /** @var \MongoClient */
    private $connection = null;

    /** @var \MongoDB */
    protected $db = null;

    private function connect()
    {
        if ($this->connected === false || is_null($this->connection)) {
            $this->connection = new \MongoClient("mongodb://{$this->host}:{$this->port}");
            if (!is_null($this->dbName)) {
                $this->db = $this->connection->selectDB($this->dbName);
                if (!empty($this->login)) {
                    $this->db->authenticate($this->login, $this->password);
                }
            } else {
                throw new \Exception("Database not defined");
            }
        }
    }

    public function getConnection()
    {
        $this->connect();
        return $this->connection;
    }

    public function setDbName($database)
    {
        $this->connected = false;
        $this->dbName = $database;
    }

    public function setHost($host)
    {
        $this->connected = false;
        $this->host = $host;
    }

    public function setLogin($login)
    {
        $this->connected = false;
        $this->login = $login;
    }

    public function setPassword($password)
    {
        $this->connected = false;
        $this->password = $password;
    }

    public function setPort($port)
    {
        $this->connected = false;
        $this->port = $port;
    }

    /**
     * @return \MongoDB
     */
    public function getDb()
    {
        $this->connect();
        return $this->db;
    }
} 