<?php
include_once "DatabaseHandler.php";
abstract class Account {
    private $firstName;
    private $lastName;
    private $email;

    public function login($userID, $password) {
        $dbHandler = DatabaseHandler::getInstance();
        $res = $dbHandler->execQuery("SELECT * FROM users WHERE id='" . $userID . "';");
        $row = $res->fetch_assoc();
        $storedPW = $row['password'];
        //TODO: Compare Password hashes, if true return object of Employee or Customer
    }

    abstract public function logout(); //TODO

    public function register($email, $name, $password, $isEmployee) {
        return "ERROR: Not implemented yet";
    }
}
?>
