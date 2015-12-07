<?php

require_once('../Model/Account.php');

//check whether the email format is correct. It is also done here in case client side fails to check it.
if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {

    //remove html special characters before store the data.
    $firstName = htmlentities( strip_tags ($_POST['fname']));
    $lastName = htmlentities( strip_tags ($_POST['lname']));
    $password = htmlentities( strip_tags ($_POST['password']));
    $email = htmlentities( strip_tags ($_POST['email']));
    $isEmployee = isset($_POST['yes']) ? true : false;
    $usesSCS = false;
    if (isset($_POST['tan_method']) and $_POST['tan_method'] == 'SCS')
    {
         $usesSCS = true;
    } 

    $result = Account::register($email, $firstName, $lastName, $password, $isEmployee, $usesSCS);
    if (is_string($result)) {

        echo $result;
    } else {
        echo "You have registered successfully!";
        echo "<a href='../View/index.php'>Click here to go back to the HomePage</a>";
    }
}else{

    echo "Invalid email!";

}
