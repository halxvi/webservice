<?php
require_once('dev.php');

class DB
{
    private $dbENV = null;
    private $dsn = null;
    private $pdo = null;

    function __construct()
    {
        global $check, $dbENV;
        if ($check) {
            $this->dsn = sprintf('mysql:host=%s; dbname=%s; charset=utf8', $dbENV['host'], $dbENV['dbname']);
            $this->pdo = new PDO($this->dsn, $dbENV['user'], $dbENV['pass'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        } else {
            $this->dbENV = parse_url($_SERVER["CLEARDB_DATABASE_URL"]);
            $this->dbENV['dbname'] = ltrim($this->dbENV['path'], '/');
            $this->dsn = sprintf('mysql:host=%s; dbname=%s; charset=utf8', $this->dbENV['host'], $this->dbENV['dbname']);
            $this->pdo = new PDO($this->dsn, $this->dbENV['user'], $this->dbENV['pass'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        }
    }

    function getPDO()
    {
        return $this->pdo;
    }
}
