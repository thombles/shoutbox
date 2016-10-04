<?php

require_once 'helpers.php';

class Database
{
    private static $factory;
    public static function getFactory()
    {
        if (!self::$factory)
            self::$factory = new Database();
        return self::$factory;
    }

    private $db;

    public function getConnection() {
        $db_hostname = getConfig('db_hostname');
        $db_database = getConfig('db_database');
        $db_username = getConfig('db_username');
        $db_password = getConfig('db_password');

        if (!$this->db)
	   $this->db = mysqli_connect($db_hostname, $db_username, $db_password, $db_database);
        return $this->db;
    }
}

?>