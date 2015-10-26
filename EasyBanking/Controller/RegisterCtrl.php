<?php

require_once('../Model/Account.php');


$email = $_POST['email'];
$firstName = $_POST['fname'];
$lastName = $_POST['lname'];
$password = $_POST['password'];
$isEmployee = isset($_POST['yes'])? true:false;


echo $email." ".$firstName." ".$lastName." ".$password." ".$isEmployee; //testing

// call function:  don't know how to call it now
// register($email,$firstName,$lastName,$password,$isEmployee);


//return the front page
header("Location:../View/index.php");
