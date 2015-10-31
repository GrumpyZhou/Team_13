<?php

require_once('../Model/Account.php');
$email = $_POST['email'];
$firstName = $_POST['fname'];
$lastName = $_POST['lname'];
$password = $_POST['password'];
$isEmployee = isset($_POST['yes'])? true:false;

$result=Account::register($email,$firstName,$lastName,$password,$isEmployee);
if(is_string($result)){
	
	echo "Error:".$result;}
	else{
		echo "You have registered successfully!";
		echo "<a href='../View/index.php'>Click here to go back to the HomePage<//a>";
}



