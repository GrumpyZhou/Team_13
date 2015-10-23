<?php
abstract class Account {
    private $firstName;
    private $lastName;
    private $email;

    abstract public function login($email, $password);
    abstract public function logout();

    public function register($email, $name, $password, $isEmployee) {
        return "ERROR: Not implemented yet";
    }
}
?>
