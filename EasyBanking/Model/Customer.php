<?php
include_once "Account.php";
class Customer extends Account {
    public $IBAN;

    function __construct($firstName, $lastName, $email, $IBAN) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->IBAN = $IBAN;
    }
}
?>
