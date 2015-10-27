<?php
class DatabaseHandler {
    static private $instance = null;

    private $mysqlConnection;
    private $connected = FALSE;

    static public function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    private function __construct(){}

    private function connectToDB() {
        $connected = FALSE;
        $this->mysqlConnection = new mysqli("127.0.0.1", "root", "samurai", "bank", 3306);
        if($mysqlConnection->connect_errno) {
            echo "Couldn't connect to DB: " . $mysqlConnection->connect_error;
            return FALSE;
        }
        $connected = TRUE;
        return TRUE;
    }

    public function execQuery($query) {
        if($connected == FALSE && connectToDB() == FALSE) {
            return;
        }
        $result = $mysqlConnection->query($query);
        return $result;
    }
}
?>
