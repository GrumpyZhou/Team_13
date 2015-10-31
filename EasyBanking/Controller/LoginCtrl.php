<?php
session_start();
require_once('../Model/Account.php');

if (isset($_POST['email']) && isset($_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $user =Account::login($email, $password);  
        if(is_string($user)){
			echo "Error message: ".$user;
			
			}
        else{
            $_SESSION['email'] = $user->email;
            $_SESSION['firstname'] = $user->firstName;
            $_SESSION['lastname'] = $user->lastName;
       
            if ($user instanceof Customer) {
                $_SESSION['isEmployee'] = false;
                $_SESSION['iban'] = $user->IBAN;
               // $_SESSION['balance'] = $user->balance;   
                header("Location:../View/account.php");
            } else {
                $_SESSION['isEmployee'] = true;
                header("Location:../View/administration.php");
            }
        }
    }
//header("Location:../View/account.php"); //also for test


