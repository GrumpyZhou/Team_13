<?php

require_once('../Model/Account.php');

//check whether the email format is correct. It is also done here in case client side fails to check it.
if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {

    //remove html special characters before store the data.
    $firstName = htmlspecialchars($_POST['fname']);
    $lastName = htmlspecialchars($_POST['lname']);
    $password = $_POST['password'];
    $isEmployee = isset($_POST['yes']) ? true : false;
    $email = $_POST['email'];

    $result = Account::register($email, $firstName, $lastName, $password, $isEmployee);
    if (is_string($result)) {

        echo "Error:" . $result;
    } else {
        echo "You have registered successfully!";
        echo "<a href='../View/index.php'>Click here to go back to the HomePage</a>";
    }
}else{

    echo "Invalid email!";

}

