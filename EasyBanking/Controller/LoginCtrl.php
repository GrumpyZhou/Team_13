<?php
session_start();
require_once('../Model/Account.php');

//if (!isset($_SESSION['authenticated'])) {
//    $_SESSION['isAuthenticated'] = false;   
//}

if (isset($_POST['email']) && isset($_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];


    while (false) { // the block is just for test
        $user = login($email, $password);    // Call interface to authenticate
        if (isset($user)) {
            $_SESSION['currentUser'] = $user;
            if ($user instanceof Customer) {
                $_SESSION['isEmployee'] = false;
                header("Location:../View/account.html");
            } else {
                $_SESSION['isEmployee'] = false;
                header("Location:../View/administration.html");
            }
        }
    }

  //testing ...
  header("Location:../View/account.html");
}



