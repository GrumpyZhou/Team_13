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
        $this->connected = FALSE;
        $this->mysqlConnection = new mysqli("127.0.0.1", "root", "samurai", "bank", 3306);
        if($this->mysqlConnection->connect_errno) {
            echo "Couldn't connect to DB: " . $this->mysqlConnection->connect_error;
            return FALSE;
        }
        $this->connected = TRUE;
        return TRUE;
    }

    public function execQuery($query) {
        if($this->connected == FALSE && $this->connectToDB() == FALSE) {
            return;
        }
        $result = $this->mysqlConnection->query($query);
        return $result;
    }
}
?>
