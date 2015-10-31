<?php
include_once "Account.php";
class Customer extends Account {
    public $IBAN;
    public $balance;

    function __construct($firstName, $lastName, $email, $IBAN, $balance) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->IBAN = $IBAN;
        $this->balance = $balance;
    }
}
?>
