<?php
class Employee extends Account {
    function __construct($firstName, $lastName, $email) {
        $this->$firstName = $firstName;
        $this->$lastName = $lastName;
        $this->$email = $email;
    }
}
?>
